<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialRequestItem;
use App\Models\StockTransaction;
use App\Models\StockLocation; // <-- Add this
use App\Models\Supplier;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GoodsReceiptController extends Controller
{

    public function index()
    {
        $receipts = GoodsReceipt::with('supplier', 'purchaseOrder', 'project', 'location') // Eager load location
                                ->latest()->paginate(20);
        return view('goods-receipts.index', compact('receipts'));
    }

    /**
     * UPDATED: create
     * Now fetches locations for the Non-PO form
     */
    public function create(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $projects = Project::orderBy('project_code')->get();
        $inventoryItems = InventoryItem::orderBy('item_name')->get();
        // Fetch all active locations
        $locations = StockLocation::where('is_active', true)->orderBy('name')->get();

        return view('goods-receipts.create', compact(
            'suppliers', 'projects', 'inventoryItems', 'locations'
        ));
    }

    /**
     * UPDATED: store
     * Now validates and saves stock_location_id
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receipt_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'stock_location_id' => 'required|exists:stock_locations,id', // <-- New
            'notes' => 'nullable|string',
            'items_json' => 'required|json',
        ]);

        $itemsArray = json_decode($validated['items_json'], true);

        $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        if ($itemsValidator->fails()) {
            return back()
                   ->withInput($request->except('items_json') + ['items_json' => $validated['items_json']])
                   ->withErrors($itemsValidator);
        }
        $validatedItems = $itemsValidator->validated()['items'];

        try {
            DB::beginTransaction();

            $goodsReceipt = GoodsReceipt::create([
                'receipt_date' => $validated['receipt_date'],
                'supplier_id' => $validated['supplier_id'],
                'project_id' => $validated['project_id'],
                'stock_location_id' => $validated['stock_location_id'], // <-- New
                'notes' => $validated['notes'],
                'received_by_user_id' => Auth::id(),
                'status' => 'received',
            ]);

            foreach ($validatedItems as $itemData) {

                $grnItem = $goodsReceipt->items()->create([
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity_received' => $itemData['quantity_received'],
                    'unit_cost' => $itemData['unit_cost'],
                ]);

                StockTransaction::create([
                    'inventory_item_id' => $grnItem->inventory_item_id,
                    'stock_location_id' => $goodsReceipt->stock_location_id, // <-- Changed
                    'quantity' => $grnItem->quantity_received,
                    'unit_cost' => $grnItem->unit_cost,
                    'sourceable_id' => $goodsReceipt->id,
                    'sourceable_type' => GoodsReceipt::class,
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
     * UPDATED: show
     * Now loads the 'location' relationship
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->status == 'draft') {
             return redirect()->route('goods-receipts.edit', $goodsReceipt)
                              ->with('info', 'This is a draft receipt. Please fill in the received quantities and post it.');
        }

        $goodsReceipt->load('items.inventoryItem', 'supplier', 'project', 'purchaseOrder', 'receiver', 'backOrderReceipt', 'location');
        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    /**
     * UPDATED: edit
     * Now fetches locations for the PO form
     */
    public function edit(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->status !== 'draft') {
            return redirect()->route('goods-receipts.show', $goodsReceipt)
                             ->with('error', 'This receipt has already been posted and cannot be edited.');
        }

        $goodsReceipt->load('items.inventoryItem', 'items.purchaseOrderItem', 'purchaseOrder.project');

        // Fetch all active locations
        $locations = StockLocation::where('is_active', true)->orderBy('name')->get();
        
        // --- This logic is unchanged ---
        $itemsFromPO = $goodsReceipt->items->map(function ($item) {
            // ... (rest of mapping logic is identical)
            $poItem = $item->purchaseOrderItem;
            $remainingQtyOnPO = 0;
            $qtyOrderedOnPO = 0;
            $qtyAlreadyReceivedOnPO = 0;

            if ($poItem) {
                $qtyOrderedOnPO = $poItem->quantity_ordered;
                $qtyAlreadyReceivedOnPO = $poItem->quantity_received;

                $remainingQtyOnPO = $qtyOrderedOnPO - $qtyAlreadyReceivedOnPO;
            }

            return [
                'goods_receipt_item_id' => $item->id,
                'purchase_order_item_id' => $item->purchase_order_item_id,
                'inventory_item_id' => $item->inventory_item_id,
                'item_name' => $item->inventoryItem->item_name,
                'item_code' => $item->inventoryItem->item_code,
                'uom' => $item->inventoryItem->uom,
                'quantity_ordered' => $qtyOrderedOnPO,
                'quantity_already_received_on_po' => $qtyAlreadyReceivedOnPO,
                'quantity_to_receive' => $remainingQtyOnPO,
                'max_receivable' => $remainingQtyOnPO,
                'unit_cost' => $item->unit_cost,
                'is_from_po' => true
            ];
        })->filter(fn($item) => $item['max_receivable'] > 0.001);

        if ($itemsFromPO->isEmpty()) {
             $goodsReceipt->delete();
             return redirect()->route('purchase-orders.show', $goodsReceipt->purchaseOrder)
                                     ->with('info', 'All items on this Purchase Order have already been fully received.');
        }

        return view('goods-receipts.edit', compact('goodsReceipt', 'itemsFromPO', 'locations'));
    }

    public function update(Request $request, GoodsReceipt $goodsReceipt)
    {
        return redirect()->route('goods-receipts.edit', $goodsReceipt);
    }

    /**
     * UPDATED: postReceipt
     * Now validates stock_location_id and uses it for transactions
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
            'stock_location_id' => 'required|exists:stock_locations,id', // <-- New
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
            'back_order_action' => 'required|string|in:create,close', 
        ]);

        $po = $goodsReceipt->purchaseOrder;
        $newBackOrderItems = []; 
        $createBackOrder = $validated['back_order_action'] === 'create';

        try {
            DB::beginTransaction();

            $goodsReceipt->update([
                'receipt_date' => $validated['receipt_date'],
                'notes' => $validated['notes'],
                'stock_location_id' => $validated['stock_location_id'], // <-- New
                'received_by_user_id' => Auth::id(),
                'status' => 'received',
            ]);

            foreach ($validated['items'] as $itemData) {
                $quantityReceivedNow = (float)$itemData['quantity_to_receive'];
                $grnItem = GoodsReceiptItem::find($itemData['goods_receipt_item_id']);

                $grnItem->update(['quantity_received' => $quantityReceivedNow]);

                if ($quantityReceivedNow > 0) {
                    StockTransaction::create([
                        'inventory_item_id' => $grnItem->inventory_item_id,
                        'stock_location_id' => $goodsReceipt->stock_location_id, // <-- Changed
                        'quantity' => $quantityReceivedNow,
                        'unit_cost' => $grnItem->unit_cost,
                        'sourceable_id' => $goodsReceipt->id,
                        'sourceable_type' => GoodsReceipt::class,
                    ]);

                    $poItem = PurchaseOrderItem::find($grnItem->purchase_order_item_id);
                    if ($poItem) {
                        $poItem->increment('quantity_received', $quantityReceivedNow);

                        if ($poItem->material_request_item_id) {
                            $mrItem = MaterialRequestItem::find($poItem->material_request_item_id);
                            if ($mrItem) {
                                $mrItem->increment('quantity_fulfilled', $quantityReceivedNow);
                            }
                        }
                    }
                }

                $remainingForBackOrder = (float)$itemData['max_receivable'] - $quantityReceivedNow;

                if ($remainingForBackOrder > 0.001) {
                    $newBackOrderItems[] = [
                        'purchase_order_item_id' => $grnItem->purchase_order_item_id,
                        'inventory_item_id' => $grnItem->inventory_item_id,
                        'quantity_received' => 0,
                        'unit_cost' => $grnItem->unit_cost,
                    ];
                }
            }

            if (!empty($newBackOrderItems) && $createBackOrder) {
                // Back-order receipt inherits the location from the PO,
                // or it will be set when the user receives *that* draft.
                $newDraftReceipt = GoodsReceipt::create([
                    'purchase_order_id' => $po->id,
                    'supplier_id' => $po->supplier_id,
                    'project_id' => $po->project_id,
                    'stock_location_id' => $goodsReceipt->stock_location_id, // Inherit location for now
                    'receipt_date' => now()->toDateString(),
                    'status' => 'draft',
                    'notes' => 'Back-order from ' . $goodsReceipt->receipt_no,
                ]);
                $newDraftReceipt->items()->createMany($newBackOrderItems);

                $goodsReceipt->back_order_receipt_id = $newDraftReceipt->id;
                $goodsReceipt->save();
            }

            $po->refresh();
            $po->load('items');
            
            if (!empty($newBackOrderItems) && $createBackOrder) {
                $po->status = 'partially_received';
            } else {
                $po->status = 'received';
            }
            $po->save();

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
            return back()->withInput()->withErrors('Error posting receipt: ' . $e->getMessage());
        }

        return redirect()->route('goods-receipts.show', $goodsReceipt)
                         ->with('success', 'Receipt posted successfully. Stock has been updated.');
    }
}