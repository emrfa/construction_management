<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier; 
use App\Models\InventoryItem;
use App\Models\PurchaseOrderItem;
use App\Models\StockTransaction;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('supplier')->latest()->get();
        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('item_name')->get();
        return view('purchase-orders.create', compact('suppliers', 'inventoryItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            $validatedData = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'order_date' => 'required|date',
                'expected_delivery_date' => 'required|date|after_or_equal:order_date',
                'items' => 'required|array|min:1',
                'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
                'items.*.quantity_ordered' => 'required|numeric|min:0.01',
                'items.*.unit_cost' => 'required|numeric|min:0',
            ]);

            $grandTotal = 0;

            try {
                DB::beginTransaction();

                $purchaseOrder = PurchaseOrder::create([
                    'supplier_id' => $validatedData['supplier_id'],
                    'order_date' => $validatedData['order_date'],
                    'expected_delivery_date' => $validatedData['expected_delivery_date'],
                    'status' => 'draft', // Default status
                    'total_amount' => 0, // Calculate below
                ]);

                foreach ($validatedData['items'] as $itemData) {
                    $subtotal = $itemData['quantity_ordered'] * $itemData['unit_cost'];
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'inventory_item_id' => $itemData['inventory_item_id'],
                        'quantity_ordered' => $itemData['quantity_ordered'],
                        'unit_cost' => $itemData['unit_cost'],
                        'subtotal' => $subtotal,
                    ]);
                    $grandTotal += $subtotal;
                }

                // Update the PO's total amount
                $purchaseOrder->total_amount = $grandTotal;
                $purchaseOrder->save();

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withInput()->withErrors('Error saving purchase order: ' . $e->getMessage());
            }

            // Redirect to the PO list for now
            return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order created successfully.');
        
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        // Load relationships needed for the view
        $purchaseOrder->load(['supplier', 'items.inventoryItem']); // Load supplier and items with their master data
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
        {
            // Only allow editing if PO is still a draft
            if ($purchaseOrder->status !== 'draft') {
                return redirect()->route('purchase-orders.show', $purchaseOrder)
                                ->with('error', 'Only draft Purchase Orders can be edited.');
            }

            // Load items and their master data
            $purchaseOrder->load('items.inventoryItem');

            // Get data for dropdowns
            $suppliers = Supplier::orderBy('name')->get();
            $inventoryItems = InventoryItem::orderBy('item_name')->get(); // For potentially adding items

            return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'inventoryItems'));
        }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Only allow updating if PO is still a draft
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                             ->with('error', 'Only draft Purchase Orders can be updated.');
        }

        // Validate header data (including supplier) and items
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            // Validate the raw JSON input first
            'items_json' => 'required|json',
        ]);

        // Decode the JSON items
        $itemsArray = json_decode($validatedData['items_json'], true);

        // Deeper validation on decoded items
         $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_order_items,id', // Existing item ID
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0', // Cost required in draft edit
        ]);

         if ($itemsValidator->fails()) {
            // Re-encode JSON for old() helper
            return back()
                   ->withInput($request->except('items_json') + ['items_json' => $validatedData['items_json']])
                   ->withErrors($itemsValidator);
        }
        $validatedItems = $itemsValidator->validated()['items']; // Use validated array


        $grandTotal = 0;
        // Check which button was clicked BEFORE the try block
        $markAsOrdered = $request->has('mark_ordered') && $request->input('mark_ordered') == '1';

        try {
            DB::beginTransaction();

            // Update PO Header
            $purchaseOrder->update([
                'supplier_id' => $validatedData['supplier_id'],
                'order_date' => $validatedData['order_date'],
                'expected_delivery_date' => $validatedData['expected_delivery_date'],
            ]);

            $processedItemIds = []; // Keep track of item IDs from the form

            // Update or Create PO Items
            foreach ($validatedItems as $itemData) {
                $subtotal = ($itemData['quantity_ordered'] ?? 0) * ($itemData['unit_cost'] ?? 0);
                $grandTotal += $subtotal;

                $itemPayload = [
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity_ordered' => $itemData['quantity_ordered'],
                    'unit_cost' => $itemData['unit_cost'],
                    'subtotal' => $subtotal,
                     // material_request_item_id is preserved if it existed
                ];

                if (!empty($itemData['id'])) {
                    // Update existing item belonging to this PO
                    $item = $purchaseOrder->items()->find($itemData['id']);
                    if ($item) {
                        $item->update($itemPayload);
                        $processedItemIds[] = $item->id;
                    }
                    // else: log warning or ignore if ID doesn't belong?
                } else {
                    // Create new item (ensure material_request_item_id is null if needed)
                   $itemPayload['material_request_item_id'] = $itemData['material_request_item_id'] ?? null; // Carry over if present in JS, unlikely though
                   $newItem = $purchaseOrder->items()->create($itemPayload);
                   $processedItemIds[] = $newItem->id;
                }
            }

            // Delete items associated with this PO that were NOT in the submitted form data
            $purchaseOrder->items()->whereNotIn('id', $processedItemIds)->delete();

            // Update the PO's total amount
            $purchaseOrder->total_amount = $grandTotal;

            // Update status based on button clicked
            if ($markAsOrdered) {
                // Ensure supplier is set (redundant due to validation, but safe)
                if(empty($purchaseOrder->supplier_id)) {
                     throw new \Exception("Cannot mark as ordered without selecting a supplier.");
                }
                 // Check costs AFTER updates/deletes are processed
                 // Refresh items relation to get the final list
                 $purchaseOrder->load('items');
                 $itemsWithZeroCost = $purchaseOrder->items->filter(fn($item) => $item->unit_cost <= 0)->count();

                 if ($itemsWithZeroCost > 0) {
                    throw new \Exception("Cannot mark as ordered when items have zero unit cost.");
                 }
                $purchaseOrder->status = 'ordered';
            } else {
                // Keep as draft if only "Save Draft" was clicked
                $purchaseOrder->status = 'draft';
            }

            $purchaseOrder->save(); // Save total amount and status changes

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
             \Log::error("Error updating PO [ID: {$purchaseOrder->id}]: " . $e->getMessage());
             // Re-encode JSON for old() helper
            return back()
                   ->withInput($request->except('items_json') + ['items_json' => $validatedData['items_json']])
                   ->withErrors('Error updating purchase order: ' . $e->getMessage());
        }

        $message = $markAsOrdered ? 'Purchase Order marked as Ordered successfully.' : 'Draft Purchase Order updated successfully.';
        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                // Define allowed statuses users can manually set (add more later)
                Rule::in(['ordered', 'cancelled', 'draft']),
            ],
        ]);

        $newStatus = $validated['status'];

        // Add logic checks later (e.g., cannot cancel if received)
        // For now, just update
        $purchaseOrder->status = $newStatus;
        $purchaseOrder->save();

        return redirect()->route('purchase-orders.show', $purchaseOrder)
                        ->with('success', 'PO status updated successfully.');
    }   

    /**
     * Show the form for receiving items for a Purchase Order.
     */
    public function showReceiveForm(PurchaseOrder $purchaseOrder)
    {
        // Ensure PO is in a state where it can receive items
        if (!in_array($purchaseOrder->status, ['ordered', 'partially_received'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', 'Cannot receive items for a PO that is not ordered or partially received.');
        }

        $purchaseOrder->load('items.inventoryItem'); // Load items and their master data
        return view('purchase-orders.receive', compact('purchaseOrder'));
    }

    /**
     * Process the receiving of items and update stock.
     */
    public function processReceive(Request $request, PurchaseOrder $purchaseOrder)
    {
       // 1. Validation
    $validated = $request->validate([
        'items' => 'required|array',
        'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
        'items.*.quantity_received_now' => 'required|numeric|min:0',
        // Optional: Add validation for received_date if you add that field
        // 'received_date' => 'required|date'
    ]);

    $receivedItemsData = $validated['items'];
    $somethingReceived = false;
    // Get the related Material Request ID from the PO header
    $materialRequestId = $purchaseOrder->material_request_id;
    $projectId = null; // Initialize project ID

    // If linked to a material request, find the project ID
    if ($materialRequestId) {
        $materialRequest = MaterialRequest::find($materialRequestId);
        if ($materialRequest) {
            $projectId = $materialRequest->project_id;
        }
    }

    try {
        DB::beginTransaction();

        // 2. Loop through submitted items
        foreach ($receivedItemsData as $receivedItem) {
            $quantityReceivedNow = (float) $receivedItem['quantity_received_now'];

            if ($quantityReceivedNow > 0) {
                $somethingReceived = true;
                $poItem = PurchaseOrderItem::findOrFail($receivedItem['po_item_id']);

                // Validate against remaining receivable quantity
                $maxReceivable = $poItem->quantity_ordered - $poItem->quantity_received;
                if ($quantityReceivedNow - 0.001 > $maxReceivable) { // Allow for tiny float differences
                    throw new \Exception("Cannot receive quantity ({$quantityReceivedNow}) greater than remaining ({$maxReceivable}) for PO Item ID: {$poItem->id}.");
                }

                // 3. Update PO Item quantity received
                $poItem->quantity_received += $quantityReceivedNow;
                $poItem->save();

                // 4. Create Stock Transaction (with Project ID if available)
                StockTransaction::create([
                    'inventory_item_id' => $poItem->inventory_item_id,
                    'quantity' => $quantityReceivedNow, // Positive for stock in
                    'unit_cost' => $poItem->unit_cost,
                    'sourceable_id' => $purchaseOrder->id,
                    'sourceable_type' => PurchaseOrder::class,
                    'project_id' => $projectId, // Add project ID here
                ]);

                // 5. Update linked Material Request Item (if linked)
                if ($poItem->material_request_item_id) {
                    $mrItem = MaterialRequestItem::find($poItem->material_request_item_id);
                    if ($mrItem) {
                        $mrItem->quantity_fulfilled += $quantityReceivedNow;
                        // Prevent fulfilled exceeding requested due to float issues or over-ordering allowed
                        if ($mrItem->quantity_fulfilled > $mrItem->quantity_requested) {
                            $mrItem->quantity_fulfilled = $mrItem->quantity_requested;
                        }
                        $mrItem->save();
                    }
                }
            }
        }

        // 6. Update Overall PO Status
        if ($somethingReceived) {
            $purchaseOrder->load('items'); // Reload items
            $allPoItemsReceived = $purchaseOrder->items->every(fn($item) => $item->quantity_received >= $item->quantity_ordered - 0.001); // Check if all PO items are fully received (allowing for float issues)

            if ($allPoItemsReceived) {
                $purchaseOrder->status = 'received';
            } else {
                $purchaseOrder->status = 'partially_received';
            }
            $purchaseOrder->save();
        }

        // 7. Update Overall Material Request Status (if linked)
        if ($materialRequest) {
             // Reload request items to get updated fulfilled quantities
            $materialRequest->load('items');
            $allMrItemsFulfilled = $materialRequest->items->every(fn($item) => $item->quantity_fulfilled >= $item->quantity_requested - 0.001); // Check if all requested items are fulfilled

            if ($allMrItemsFulfilled) {
                $materialRequest->status = 'fulfilled';
            } else {
                // Check if *any* fulfillment has happened
                $anyMrItemFulfilled = $materialRequest->items->some(fn($item) => $item->quantity_fulfilled > 0);
                if ($anyMrItemFulfilled && $materialRequest->status !== 'fulfilled') { // Avoid downgrading from fulfilled
                     $materialRequest->status = 'partially_fulfilled';
                }
                // If no fulfillment yet, it remains 'approved'
            }
             // Only save if status changed to avoid unnecessary updates/events
            if ($materialRequest->isDirty('status')) {
                 $materialRequest->save();
            }
        }

        DB::commit();

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error processing received items for PO ID {$purchaseOrder->id}: " . $e->getMessage());
        return back()->withInput()->withErrors('Error receiving items: ' . $e->getMessage());
    }

    // 8. Redirect back to the PO show page
    return redirect()->route('purchase-orders.show', $purchaseOrder)
                     ->with('success', 'Items received, stock updated, and material request fulfillment updated successfully.');
    }
}
