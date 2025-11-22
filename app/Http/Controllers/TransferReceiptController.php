<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TransferReceipt;
use App\Models\TransferShipment;
use App\Models\TransferShipmentItem;
use App\Models\StockTransaction;
use App\Models\StockBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferReceiptController extends Controller
{
    public function create(TransferShipment $transferShipment)
    {
        // Check permission: User must have access to Destination Location
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasAccessToLocation($transferShipment->destination_location_id)) {
            return back()->with('error', 'You do not have permission to receive at this location.');
        }

        if ($transferShipment->status === 'received') {
            return back()->with('info', 'This shipment has already been received.');
        }

        $transferShipment->load('items.inventoryItem');
        return view('transfer-receipts.create', compact('transferShipment'));
    }

    public function store(Request $request, TransferShipment $transferShipment)
    {
        $validated = $request->validate([
            'received_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.shipment_item_id' => 'required|exists:transfer_shipment_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create Receipt Header
            $receipt = TransferReceipt::create([
                'receipt_number' => 'RCP-' . strtoupper(uniqid()),
                'transfer_shipment_id' => $transferShipment->id,
                'received_date' => $validated['received_date'],
                'received_by_user_id' => Auth::id(),
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['items'] as $itemData) {
                $shipmentItemId = $itemData['shipment_item_id'];
                $qtyReceived = $itemData['quantity_received'];

                $shipmentItem = TransferShipmentItem::find($shipmentItemId);
                
                // Update Shipment Item
                $shipmentItem->quantity_received = $qtyReceived; // Assuming full receipt or manual entry. 
                // If partial receipt is allowed repeatedly, we need to increment. 
                // But for now, let's assume one-time receipt per shipment for simplicity, or we overwrite.
                // The plan implies "The In Document". Usually one shipment = one receipt event.
                $shipmentItem->save();

                if ($qtyReceived > 0) {
                    // 2. Add Stock to Destination (Transaction + WAC Update)
                    $transaction = StockTransaction::create([
                        'inventory_item_id' => $shipmentItem->inventory_item_id,
                        'stock_location_id' => $transferShipment->destination_location_id,
                        'quantity' => $qtyReceived,
                        'unit_cost' => $shipmentItem->unit_cost, // Use cost from shipment (Source WAC)
                        'sourceable_type' => TransferReceipt::class,
                        'sourceable_id' => $receipt->id,
                    ]);

                    // Update Balance (WAC)
                    $balance = StockBalance::firstOrNew([
                        'inventory_item_id' => $shipmentItem->inventory_item_id,
                        'stock_location_id' => $transferShipment->destination_location_id,
                    ]);

                    $currentQty = $balance->quantity ?? 0;
                    $currentAvg = $balance->average_unit_cost ?? 0;
                    
                    $totalValue = ($currentQty * $currentAvg) + ($qtyReceived * $shipmentItem->unit_cost);
                    $totalQty = $currentQty + $qtyReceived;

                    $balance->quantity = $totalQty;
                    if ($totalQty > 0) {
                        $balance->average_unit_cost = $totalValue / $totalQty;
                    }
                    $balance->last_transaction_id = $transaction->id;
                    $balance->save();
                }
            }

            // 3. Update Shipment Status
            $transferShipment->status = 'received';
            $transferShipment->save();

            // 4. Update Internal Transfer Status (Check for Completion)
            $internalTransfer = $transferShipment->internalTransfer;
            if ($internalTransfer) {
                // Check if all items in the Internal Transfer are fully shipped AND received
                $internalTransfer->load('items', 'shipments');
                
                $allShipped = $internalTransfer->items->every(fn($item) => $item->quantity_shipped >= $item->quantity_requested - 0.001);
                $allShipmentsReceived = $internalTransfer->shipments->every(fn($shp) => $shp->status === 'received');
                
                if ($allShipped && $allShipmentsReceived) {
                    $internalTransfer->status = 'completed';
                    $internalTransfer->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error receiving shipment: ' . $e->getMessage());
        }

        return redirect()->route('transfer-shipments.show', $transferShipment)
                         ->with('success', 'Shipment received successfully. Stock added to destination.');
    }
}
