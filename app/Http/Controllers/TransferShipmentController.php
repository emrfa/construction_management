<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TransferShipment;
use App\Models\TransferShipmentItem;
use App\Models\InternalTransfer;
use App\Models\StockTransaction;
use App\Models\StockBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferShipmentController extends Controller
{
    public function create(InternalTransfer $internalTransfer)
    {
        // Check permission: User must have access to Source Location
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasAccessToLocation($internalTransfer->source_location_id)) {
            return back()->with('error', 'You do not have permission to ship from this location.');
        }

        $internalTransfer->load('items.inventoryItem');
        
        // Calculate remaining quantities to ship
        $itemsToShip = $internalTransfer->items->map(function($item) use ($internalTransfer) {
            $remaining = $item->quantity_requested - $item->quantity_shipped;
            if ($remaining <= 0) return null;

            // Get Current Stock at Source for validation/display
            $balance = StockBalance::where('inventory_item_id', $item->inventory_item_id)
                ->where('stock_location_id', $internalTransfer->source_location_id)
                ->first();
            
            $onHand = $balance ? $balance->quantity : 0;

            return [
                'item_id' => $item->inventory_item_id,
                'name' => $item->inventoryItem->item_name,
                'code' => $item->inventoryItem->item_code,
                'uom' => $item->inventoryItem->uom,
                'requested' => $item->quantity_requested,
                'shipped_so_far' => $item->quantity_shipped,
                'remaining' => $remaining,
                'on_hand' => $onHand,
            ];
        })->filter();

        if ($itemsToShip->isEmpty()) {
            return back()->with('info', 'All items in this transfer have already been shipped.');
        }

        return view('transfer-shipments.create', compact('internalTransfer', 'itemsToShip'));
    }

    public function store(Request $request, InternalTransfer $internalTransfer)
    {
        $validated = $request->validate([
            'shipped_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.internal_transfer_item_id' => 'required|exists:internal_transfer_items,id',
            'items.*.quantity_shipped' => 'nullable|numeric|min:0', // Changed to nullable/min:0 to allow skipping items, but we filter below
        ]);

        try {
            DB::beginTransaction();

            // Filter out items with 0 quantity
            $itemsToProcess = collect($validated['items'])->filter(function($item) {
                return isset($item['quantity_shipped']) && $item['quantity_shipped'] > 0;
            });

            if ($itemsToProcess->isEmpty()) {
                throw new \Exception("Please specify a quantity to ship for at least one item.");
            }

            // 1. Create Shipment Header
            $shipment = TransferShipment::create([
                'shipment_number' => 'SHP-' . strtoupper(uniqid()),
                'internal_transfer_id' => $internalTransfer->id,
                'source_location_id' => $internalTransfer->source_location_id,
                'destination_location_id' => $internalTransfer->destination_location_id,
                'shipped_date' => $validated['shipped_date'],
                'status' => 'in_transit',
                'shipped_by_user_id' => Auth::id(),
                'notes' => $validated['notes'],
            ]);

            foreach ($itemsToProcess as $itemData) {
                $transferItemId = $itemData['internal_transfer_item_id'];
                $qtyToShip = $itemData['quantity_shipped'];

                // Get the Transfer Item
                $transferItem = \App\Models\InternalTransferItem::findOrFail($transferItemId);
                $itemId = $transferItem->inventory_item_id;

                // 2. Validate Stock & Get WAC
                $balance = StockBalance::where('inventory_item_id', $itemId)
                    ->where('stock_location_id', $internalTransfer->source_location_id)
                    ->first();
                
                $currentQty = $balance ? $balance->quantity : 0;
                
                // Allow a small tolerance for float comparison or strict check? 
                // Let's be strict but helpful.
                if ($currentQty < $qtyToShip) {
                    throw new \Exception("Insufficient stock for Item '{$transferItem->inventoryItem->item_name}'. Available: " . number_format($currentQty, 2) . ", Trying to ship: " . number_format($qtyToShip, 2));
                }
                
                $unitCost = $balance ? $balance->average_unit_cost : 0;

                // 3. Create Shipment Item
                TransferShipmentItem::create([
                    'transfer_shipment_id' => $shipment->id,
                    'inventory_item_id' => $itemId,
                    'quantity_shipped' => $qtyToShip,
                    'quantity_received' => 0,
                    'unit_cost' => $unitCost, // Carry WAC
                ]);

                // 4. Update Internal Transfer Item (Track progress)
                $transferItem->increment('quantity_shipped', $qtyToShip);

                // 5. Deduct Stock from Source (Transaction + Balance Update)
                $transaction = StockTransaction::create([
                    'inventory_item_id' => $itemId,
                    'stock_location_id' => $internalTransfer->source_location_id,
                    'quantity' => -$qtyToShip,
                    'unit_cost' => $unitCost,
                    'sourceable_type' => TransferShipment::class,
                    'sourceable_id' => $shipment->id,
                ]);

                if ($balance) {
                    $balance->quantity -= $qtyToShip;
                    $balance->last_transaction_id = $transaction->id;
                    $balance->save();
                }
            }

            // 6. Update Internal Transfer Status
            // Check if fully shipped
            $internalTransfer->refresh();
            $allShipped = $internalTransfer->items->every(fn($item) => $item->quantity_shipped >= $item->quantity_requested - 0.001);
            
            // Update status to 'processing' (In Transit) if it was draft
            // We don't mark as 'completed' yet, as that implies Received.
            if ($internalTransfer->status === 'draft') {
                $internalTransfer->status = 'processing';
            }
            
            $internalTransfer->save();

            // 7. Update Linked Material Request (if any)
            if ($internalTransfer->material_request_id) {
                $mr = $internalTransfer->materialRequest;
                
                // Update fulfilled quantities on MR Items
                foreach ($itemsToProcess as $itemData) {
                    $transferItemId = $itemData['internal_transfer_item_id'];
                    $qtyShipped = $itemData['quantity_shipped'];
                    
                    $transferItem = \App\Models\InternalTransferItem::find($transferItemId);
                    if ($transferItem) {
                        // Find corresponding MR Item
                        $mrItem = $mr->items()->where('inventory_item_id', $transferItem->inventory_item_id)->first();
                        if ($mrItem) {
                            $mrItem->increment('quantity_fulfilled', $qtyShipped);
                        }
                    }
                }

                // Update MR Status
                $mr->refresh();
                $allFulfilled = $mr->items->every(fn($item) => $item->quantity_fulfilled >= $item->quantity_requested - 0.001);
                $anyFulfilled = $mr->items->some(fn($item) => $item->quantity_fulfilled > 0);

                if ($allFulfilled) {
                    $mr->status = 'fulfilled';
                } elseif ($anyFulfilled) {
                    $mr->status = 'partially_fulfilled';
                }
                $mr->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating shipment: ' . $e->getMessage());
        }

        return redirect()->route('internal-transfers.show', $internalTransfer)
                         ->with('success', 'Shipment created successfully. Stock deducted from source.');
    }

    public function show(TransferShipment $transferShipment)
    {
        $transferShipment->load(['items.inventoryItem', 'sourceLocation', 'destinationLocation', 'shippedBy', 'receipts']);
        return view('transfer-shipments.show', compact('transferShipment'));
    }
}
