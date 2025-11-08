<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialRequestItem;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GoodsReceiptController extends Controller
{
    /**
     * Display a listing of all receipts (draft and received).
     */
    public function index()
    {
        $receipts = GoodsReceipt::with('supplier', 'purchaseOrder', 'project')
                                ->latest()->paginate(20);
        return view('goods-receipts.index', compact('receipts'));
    }

    /**
     * Show the form for creating a new NON-PO receipt.
     */
    public function create(Request $request)
    {
        // This form is ONLY for non-PO receipts
        $suppliers = Supplier::orderBy('name')->get();
        $projects = Project::orderBy('project_code')->get();
        $inventoryItems = InventoryItem::orderBy('item_name')->get();

        return view('goods-receipts.create', compact(
            'suppliers', 'projects', 'inventoryItems'
        ));
    }

    /**
     * Store a new NON-PO receipt. This posts immediately to stock.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receipt_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $goodsReceipt = GoodsReceipt::create([
                'receipt_date' => $validated['receipt_date'],
                'supplier_id' => $validated['supplier_id'],
                'project_id' => $validated['project_id'],
                'notes' => $validated['notes'],
                'received_by_user_id' => Auth::id(),
                'status' => 'received', // Non-PO receipts are posted immediately
            ]);

            foreach ($validated['items'] as $itemData) {
                // 1. Create the Goods Receipt Item
                $grnItem = $goodsReceipt->items()->create([
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity_received' => $itemData['quantity_received'],
                    'unit_cost' => $itemData['unit_cost'],
                ]);

                // 2. Create the StockTransaction
                StockTransaction::create([
                    'inventory_item_id' => $grnItem->inventory_item_id,
                    'quantity' => $grnItem->quantity_received,
                    'unit_cost' => $grnItem->unit_cost,
                    'sourceable_id' => $goodsReceipt->id,
                    'sourceable_type' => GoodsReceipt::class,
                    'project_id' => $goodsReceipt->project_id,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error saving receipt: ' . $e->getMessage());
        }

        return redirect()->route('goods-receipts.show', $goodsReceipt)
                         ->with('success', 'Non-PO receipt posted and stock updated successfully.');
    }

    /**
     * Display a single POSTED/RECEIVED receipt.
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->status == 'draft') {
             return redirect()->route('goods-receipts.edit', $goodsReceipt)
                              ->with('info', 'This is a draft receipt. Please fill in the received quantities and post it.');
        }
        
        $goodsReceipt->load('items.inventoryItem', 'supplier', 'project', 'purchaseOrder', 'receiver');
        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    /**
     * Show the form for "fulfilling" a draft PO-based receipt.
     */
    public function edit(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->status !== 'draft') {
            return redirect()->route('goods-receipts.show', $goodsReceipt)
                             ->with('error', 'This receipt has already been posted and cannot be edited.');
        }

        $goodsReceipt->load('items.inventoryItem', 'items.purchaseOrderItem', 'purchaseOrder');

        // Pre-fill items for the form
        $itemsFromPO = $goodsReceipt->items->map(function ($item) {
            $poItem = $item->purchaseOrderItem;
            $remainingQtyOnPO = 0;
            $qtyOrderedOnPO = 0;
            $qtyAlreadyReceivedOnPO = 0;

            if ($poItem) {
                $qtyOrderedOnPO = $poItem->quantity_ordered;
                $qtyAlreadyReceivedOnPO = $poItem->quantity_received;
                // This is the max this *specific* draft can receive
                $remainingQtyOnPO = $qtyOrderedOnPO - $qtyAlreadyReceivedOnPO;
            }

            return [
                'goods_receipt_item_id' => $item->id, // This is the key
                'purchase_order_item_id' => $item->purchase_order_item_id,
                'inventory_item_id' => $item->inventory_item_id,
                'item_name' => $item->inventoryItem->item_name,
                'item_code' => $item->inventoryItem->item_code,
                'uom' => $item->inventoryItem->uom,
                'quantity_ordered' => $qtyOrderedOnPO,
                'quantity_already_received_on_po' => $qtyAlreadyReceivedOnPO, // For display
                'quantity_to_receive' => $remainingQtyOnPO, // Default to receiving all remaining
                'max_receivable' => $remainingQtyOnPO, // Validation helper
                'unit_cost' => $item->unit_cost,
                'is_from_po' => true
            ];
        })->filter(fn($item) => $item['max_receivable'] > 0.001); // Only show items that still need receiving

        if ($itemsFromPO->isEmpty()) {
             // This draft is now empty because all items were received on *other* drafts.
             // We can safely delete this empty draft.
             $goodsReceipt->delete();
             return redirect()->route('purchase-orders.show', $goodsReceipt->purchaseOrder)
                                     ->with('info', 'All items on this Purchase Order have already been fully received.');
        }

        return view('goods-receipts.edit', compact('goodsReceipt', 'itemsFromPO'));
    }

    /**
     * This method is NOT USED for posting receipts. See 'postReceipt'.
     * This is only here to satisfy the Resource Controller routes.
     */
    public function update(Request $request, GoodsReceipt $goodsReceipt)
    {
        // Redirect to the correct action
        return redirect()->route('goods-receipts.edit', $goodsReceipt);
    }
    
    /**
     * This is the new, smart update logic for posting a draft receipt.
     */
    public function postReceipt(Request $request, GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->status !== 'draft') {
            return redirect()->route('goods-receipts.show', $goodsReceipt)
                             ->with('error', 'This receipt has already been posted.');
        }

        $validated = $request->validate([
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
            // 'create_back_order' validation is REMOVED
            'items' => [
                'required', 'array', 'min:1',
                function ($attribute, $value, $fail) {
                    $totalReceived = collect($value)->sum(fn($item) => (float)($item['quantity_to_receive'] ?? 0));
                    if ($totalReceived <= 0) {
                        $fail('You must receive a quantity greater than 0 for at least one item.');
                    }
                },
            ],
            'items.*.goods_receipt_item_id' => 'required|exists:goods_receipt_items,id',
            'items.*.max_receivable' => 'required|numeric',
            'items.*.quantity_to_receive' => [
                'required', 'numeric', 'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $index = str_replace(['items.', '.quantity_to_receive'], '', $attribute);
                    $max = $request->input("items.$index.max_receivable");
                    if ((float)$value > (float)$max + 0.001) {
                        $fail("The quantity for item at row " . ($index + 1) . " ($value) cannot be greater than the max receivable ($max).");
                    }
                },
            ],
        ]);

        $po = $goodsReceipt->purchaseOrder;
        $newBackOrderItems = []; // To store items for the new draft

        try {
            DB::beginTransaction();

            // 1. Update the current receipt and mark as 'received'
            $goodsReceipt->update([
                'receipt_date' => $validated['receipt_date'],
                'notes' => $validated['notes'],
                'received_by_user_id' => Auth::id(),
                'status' => 'received',
            ]);

            // 2. Process all item updates
            foreach ($validated['items'] as $itemData) {
                $quantityReceivedNow = (float)$itemData['quantity_to_receive'];
                $grnItem = GoodsReceiptItem::find($itemData['goods_receipt_item_id']);
                
                // Update this receipt item with the actual received quantity
                $grnItem->update(['quantity_received' => $quantityReceivedNow]);

                if ($quantityReceivedNow > 0) {
                    // Create StockTransaction
                    StockTransaction::create([
                        'inventory_item_id' => $grnItem->inventory_item_id,
                        'quantity' => $quantityReceivedNow,
                        'unit_cost' => $grnItem->unit_cost,
                        'sourceable_id' => $goodsReceipt->id,
                        'sourceable_type' => GoodsReceipt::class,
                        'project_id' => $goodsReceipt->project_id,
                    ]);

                    // Update PO Item (increment)
                    $poItem = PurchaseOrderItem::find($grnItem->purchase_order_item_id);
                    if ($poItem) {
                        $poItem->increment('quantity_received', $quantityReceivedNow);

                        // Update MR Item (increment)
                        if ($poItem->material_request_item_id) {
                            $mrItem = MaterialRequestItem::find($poItem->material_request_item_id);
                            if ($mrItem) {
                                $mrItem->increment('quantity_fulfilled', $quantityReceivedNow);
                            }
                        }
                    }
                }
                
                // 4. Check if a back-order is needed
                $remainingForBackOrder = (float)$itemData['max_receivable'] - $quantityReceivedNow;
                
                // We AUTOMATICALLY create a back-order if there is a remainder
                if ($remainingForBackOrder > 0.001) {
                    $newBackOrderItems[] = [
                        'purchase_order_item_id' => $grnItem->purchase_order_item_id,
                        'inventory_item_id' => $grnItem->inventory_item_id,
                        'quantity_received' => 0, // This is a new draft
                        'unit_cost' => $grnItem->unit_cost,
                    ];
                }
            }

            // 5. Create the new back-order draft (if needed)
            if (!empty($newBackOrderItems)) {
                $newDraftReceipt = GoodsReceipt::create([
                    'purchase_order_id' => $po->id,
                    'supplier_id' => $po->supplier_id,
                    'project_id' => $po->project_id,
                    'receipt_date' => now()->toDateString(),
                    'status' => 'draft',
                    'notes' => 'Back-order from ' . $goodsReceipt->receipt_no,
                ]);
                $newDraftReceipt->items()->createMany($newBackOrderItems);
            }

            // --- 6. *** THE BUG FIX *** ---
            // Refresh PO and its items to get the new totals
            $po->refresh(); 
            $po->load('items');

            $allPoItemsReceived = $po->items->every(fn($item) => $item->quantity_received >= $item->quantity_ordered - 0.001);

            // If a new draft was created, status is 'partially_received'
            // If no new draft was created (meaning all items were received OR
            // the remaining items were 0), mark as 'received'
            if (!empty($newBackOrderItems)) {
                $po->status = 'partially_received';
            } else {
                $po->status = 'received';
            }
            $po->save();

            // Refresh Material Request (if it exists)
            if ($po->materialRequest) {
                $mr = $po->materialRequest;
                $mr->refresh();
                $mr->load('items');
                
                foreach($mr->items as $mrItem) {
                    if ($mrItem->quantity_fulfilled > $mrItem->quantity_requested) {
                        $mrItem->quantity_fulfilled = $mrItem->quantity_requested;
                        $mrItem->save();
                    }
                }

                $allMrItemsFulfilled = $mr->items->every(fn($item) => $item->quantity_fulfilled >= $item->quantity_requested - 0.001);
                
                if ($allMrItemsFulfilled) {
                    $mr->status = 'fulfilled';
                } else if ($mr->items->some(fn($item) => $item->quantity_fulfilled > 0)) {
                    $mr->status = 'partially_fulfilled';
                }
                $mr->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error posting receipt: Read  ' . $e->getMessage());
        }

        return redirect()->route('goods-receipts.show', $goodsReceipt)
                         ->with('success', 'Receipt posted successfully. Stock has been updated.');
    }
}