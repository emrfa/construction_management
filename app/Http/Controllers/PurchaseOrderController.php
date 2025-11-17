<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier; 
use App\Models\InventoryItem;
use App\Models\PurchaseOrderItem;
use App\Models\StockTransaction;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Project;
use App\Models\StockLocation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start query
        $query = PurchaseOrder::with('supplier')->latest();
        
        // **NEW**: Apply search logic
        $query->when($request->search, function ($q, $search) {
            return $q->where('po_number', 'like', "%{$search}%")
                     ->orWhereHas('supplier', function ($subQuery) use ($search) {
                         $subQuery->where('name', 'like', "%{$search}%");
                     });
        });
        
        // [MODIFIED] Paginate the query
        $purchaseOrders = $query->paginate(15)->appends($request->query());
        
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
                'expected_delivery_date' => 'nullable|date|after_or_equal:order_date', // Made nullable
                'items_json' => 'required|json', // Use items_json for consistency
            ]);

            $itemsArray = json_decode($validatedData['items_json'], true);

            $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
                'items' => 'required|array|min:1',
                'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
                'items.*.quantity_ordered' => 'required|numeric|min:0.01',
                'items.*.unit_cost' => 'required|numeric|min:0',
            ]);

            if ($itemsValidator->fails()) {
                return back()
                       ->withInput($request->except('items_json') + ['items_json' => $validatedData['items_json']])
                       ->withErrors($itemsValidator);
            }
            $validatedItems = $itemsValidator->validated()['items'];

            $grandTotal = 0;
            $markAsOrdered = $request->has('mark_ordered') && $request->input('mark_ordered') == '1';
            $status = $markAsOrdered ? 'ordered' : 'draft';

            try {
                DB::beginTransaction();

                if ($markAsOrdered) {
                    if(empty($validatedData['supplier_id'])) {
                         throw new \Exception("Cannot mark as ordered without selecting a supplier.");
                    }
                     $itemsWithZeroCost = collect($validatedItems)->filter(fn($item) => $item['unit_cost'] <= 0)->count();
                     if ($itemsWithZeroCost > 0) {
                        throw new \Exception("Cannot mark as ordered when items have zero unit cost.");
                     }
                }

                $purchaseOrder = PurchaseOrder::create([
                    'supplier_id' => $validatedData['supplier_id'],
                    'order_date' => $validatedData['order_date'],
                    'expected_delivery_date' => $validatedData['expected_delivery_date'],
                    'status' => $status,
                    'total_amount' => 0, // Will be updated
                ]);

                foreach ($validatedItems as $itemData) {
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

                $purchaseOrder->total_amount = $grandTotal;
                $purchaseOrder->save();

                if ($markAsOrdered) {
                    $this->createDraftGoodsReceipt($purchaseOrder);
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withInput()->withErrors('Error saving purchase order: ' . $e->getMessage());
            }

            $message = $markAsOrdered ? 'Purchase Order marked as Ordered and draft receipt created.' : 'Draft Purchase Order created successfully.';
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', $message);
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
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                             ->with('error', 'Only draft Purchase Orders can be updated.');
        }

        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'items_json' => 'required|json',
        ]);

        $itemsArray = json_decode($validatedData['items_json'], true);

         $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_order_items,id',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

         if ($itemsValidator->fails()) {
            return back()
                   ->withInput($request->except('items_json') + ['items_json' => $validatedData['items_json']])
                   ->withErrors($itemsValidator);
        }
        $validatedItems = $itemsValidator->validated()['items'];

        $grandTotal = 0;
        $markAsOrdered = $request->has('mark_ordered') && $request->input('mark_ordered') == '1';

        try {
            DB::beginTransaction();

            $purchaseOrder->update([
                'supplier_id' => $validatedData['supplier_id'],
                'order_date' => $validatedData['order_date'],
                'expected_delivery_date' => $validatedData['expected_delivery_date'],
            ]);

            $processedItemIds = [];

            foreach ($validatedItems as $itemData) {
                $subtotal = ($itemData['quantity_ordered'] ?? 0) * ($itemData['unit_cost'] ?? 0);
                $grandTotal += $subtotal;

                $itemPayload = [
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity_ordered' => $itemData['quantity_ordered'],
                    'unit_cost' => $itemData['unit_cost'],
                    'subtotal' => $subtotal,
                ];

                if (!empty($itemData['id'])) {
                    $item = $purchaseOrder->items()->find($itemData['id']);
                    if ($item) {
                        $item->update($itemPayload);
                        $processedItemIds[] = $item->id;
                    }
                } else {
                   $itemPayload['material_request_item_id'] = $itemData['material_request_item_id'] ?? null;
                   $newItem = $purchaseOrder->items()->create($itemPayload);
                   $processedItemIds[] = $newItem->id;
                }
            }

            $purchaseOrder->items()->whereNotIn('id', $processedItemIds)->delete();
            $purchaseOrder->total_amount = $grandTotal;

            if ($markAsOrdered) {
                if(empty($purchaseOrder->supplier_id)) {
                     throw new \Exception("Cannot mark as ordered without selecting a supplier.");
                }
                 $purchaseOrder->load('items');
                 $itemsWithZeroCost = $purchaseOrder->items->filter(fn($item) => $item->unit_cost <= 0)->count();
                 if ($itemsWithZeroCost > 0) {
                    throw new \Exception("Cannot mark as ordered when items have zero unit cost.");
                 }
                $purchaseOrder->status = 'ordered';
                
                // Use the new helper method
                $this->createDraftGoodsReceipt($purchaseOrder);

            } else {
                $purchaseOrder->status = 'draft';
            }

            $purchaseOrder->save();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
             \Log::error("Error updating PO [ID: {$purchaseOrder->id}]: " . $e->getMessage());
            return back()
                   ->withInput($request->except('items_json') + ['items_json' => $validatedData['items_json']])
                   ->withErrors('Error updating purchase order: ' . $e->getMessage());
        }

        $message = $markAsOrdered ? 'Purchase Order marked as Ordered and draft receipt created.' : 'Draft Purchase Order updated successfully.';
        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', $message);
    }

    private function createDraftGoodsReceipt(PurchaseOrder $purchaseOrder)
    {
        $existingReceipt = GoodsReceipt::where('purchase_order_id', $purchaseOrder->id)
                                       ->where('status', 'draft')
                                       ->exists();

        if ($existingReceipt) {
            return; // A draft already exists, do nothing
        }

        // --- NEW LOGIC ---
        $locationId = null;
        if ($purchaseOrder->project_id) {
            // This PO is for a project. Find that project's location.
            $projectLocation = Project::find($purchaseOrder->project_id)?->stockLocation;
            $locationId = $projectLocation?->id;
        }
        
        if (!$locationId) {
            // Not a project PO, or project has no location.
            // Default to the "Main Warehouse"
            $locationId = StockLocation::where('code', 'WH-MAIN')->value('id');
        }
        // --- END NEW LOGIC ---

        $goodsReceipt = GoodsReceipt::create([
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'project_id' => $purchaseOrder->project_id,
            'stock_location_id' => $locationId, // <-- Set the found ID
            'receipt_date' => $purchaseOrder->expected_delivery_date ?? now()->toDateString(),
            'status' => 'draft',
            'notes' => 'Auto-generated from PO ' . $purchaseOrder->po_number,
        ]);

        foreach ($purchaseOrder->items as $poItem) {
            GoodsReceiptItem::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'purchase_order_item_id' => $poItem->id,
                'inventory_item_id' => $poItem->inventory_item_id,
                'quantity_received' => 0, 
                'unit_cost' => $poItem->unit_cost,
            ]);
        }
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
                Rule::in(['ordered', 'cancelled', 'draft']),
            ],
        ]);

        $newStatus = $validated['status'];
        $message = 'PO status updated successfully.';

        if ($newStatus == 'ordered') {
            // Add validation checks before marking as ordered
            $purchaseOrder->load('items');
            if(empty($purchaseOrder->supplier_id)) {
                return back()->with('error', 'Cannot mark as ordered without a supplier.');
            }
            $itemsWithZeroCost = $purchaseOrder->items->filter(fn($item) => $item->unit_cost <= 0)->count();
            if ($itemsWithZeroCost > 0) {
                return back()->with('error', 'Cannot mark as ordered when items have zero unit cost.');
            }

            // All checks passed, create the receipt
            $this->createDraftGoodsReceipt($purchaseOrder);
            $message = 'Purchase Order marked as Ordered and draft receipt created.';
        }
        
        $purchaseOrder->status = $newStatus;
        $purchaseOrder->save();

        return redirect()->route('purchase-orders.show', $purchaseOrder)
                        ->with('success', $message);
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
    $projectId = $purchaseOrder->project_id; // Initialize project ID

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
                    'project_id' => $projectId, 
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
