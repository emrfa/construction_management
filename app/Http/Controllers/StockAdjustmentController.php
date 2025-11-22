<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockLocation;
use App\Models\InventoryItem;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource. (The Log)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // Start query
        $query = StockAdjustment::with('location', 'user')->latest();
        
        // [PERMISSION] Filter by user's location access
        if (!$user->hasRole('admin')) {
            $userLocationIds = $user->stockLocations->pluck('id');
            $query->whereIn('stock_location_id', $userLocationIds);
        }
        
        // **NEW**: Apply search logic
        $query->when($request->search, function ($q, $search) {
            return $q->where('adjustment_no', 'like', "%{$search}%")
                     ->orWhere('reason', 'like', "%{$search}%")
                     ->orWhereHas('user', function ($subQuery) use ($search) {
                         $subQuery->where('name', 'like', "%{$search}%");
                     })
                     ->orWhereHas('location', function ($subQuery) use ($search) {
                         $subQuery->where('name', 'like', "%{$search}%");
                     });
        });
        
        // [MODIFIED] Paginate the query
        $adjustments = $query->paginate(20)->appends($request->query());
        
        return view('stock-adjustments.index', compact('adjustments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        // Fetch active locations filtered by permission
        if ($user->hasRole('admin')) {
            $locations = StockLocation::where('is_active', true)->orderBy('name')->get();
        } else {
            $locations = $user->stockLocations()->where('is_active', true)->orderBy('name')->get();
        }
        
        // We will fetch items dynamically via an API for performance
        
        return view('stock-adjustments.create', compact('locations'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'adjustment_date' => 'required|date',
            'stock_location_id' => 'required|exists:stock_locations,id',
            'reason' => 'required|string|min:5',
            'items_json' => 'required|json',
        ]);

        $itemsArray = json_decode($validated['items_json'], true);

        $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.physical_qty' => 'required|numeric|min:0',
        ]);
        
        if ($itemsValidator->fails()) {
            return back()
                   ->withInput($request->except('items_json') + ['items_json' => $validated['items_json']])
                   ->withErrors($itemsValidator);
        }
        $validatedItems = $itemsValidator->validated()['items'];

        try {
            DB::beginTransaction();

            $adjustment = StockAdjustment::create([
                'adjustment_date' => $validated['adjustment_date'],
                'stock_location_id' => $validated['stock_location_id'],
                'reason' => $validated['reason'],
                'user_id' => Auth::id(),
            ]);

            foreach ($validatedItems as $itemData) {
                $itemId = $itemData['item_id'];
                $physicalQty = (float)$itemData['physical_qty'];
                $locationId = $validated['stock_location_id'];

                // 1. Get current system quantity at this location
                $systemQty = (float)StockTransaction::where('stock_location_id', $locationId)
                                 ->where('inventory_item_id', $itemId)
                                 ->sum('quantity');
                
                $adjustmentQty = $physicalQty - $systemQty;

                if (abs($adjustmentQty) < 0.001) {
                    continue; // Skip if no change
                }

                // 2. Get the current average cost for this item at this location
                $avgCost = (float)StockTransaction::where('stock_location_id', $locationId)
                                ->where('inventory_item_id', $itemId)
                                ->where('quantity', '>', 0) // Only look at IN transactions for cost
                                ->avg('unit_cost');
                
                if ($avgCost <= 0) {
                    // If no cost, try to get the item's base price
                    $avgCost = InventoryItem::find($itemId)->base_purchase_price ?? 0;
                }

                // 3. Create the Adjustment Item log
                $adjustment->items()->create([
                    'inventory_item_id' => $itemId,
                    'system_qty' => $systemQty,
                    'physical_qty' => $physicalQty,
                    'adjustment_qty' => $adjustmentQty,
                    'unit_cost' => $avgCost,
                ]);

                // 4. Create the final Stock Transaction
                // 4. Create the final Stock Transaction
                $transaction = StockTransaction::create([
                    'inventory_item_id' => $itemId,
                    'stock_location_id' => $locationId,
                    'quantity' => $adjustmentQty,
                    'unit_cost' => $avgCost,
                    'sourceable_id' => $adjustment->id,
                    'sourceable_type' => StockAdjustment::class,
                ]);

                // [NEW] Update Stock Balance
                $balance = \App\Models\StockBalance::firstOrNew([
                    'inventory_item_id' => $itemId,
                    'stock_location_id' => $locationId,
                ]);

                $currentQtyBal = $balance->quantity ?? 0;
                $currentAvgBal = $balance->average_unit_cost ?? 0;

                if ($adjustmentQty > 0) {
                    // GAIN: Treat as incoming stock (recalculate WAC)
                    // Note: We are using $avgCost (which was derived from history or base price) as the "incoming cost"
                    // Ideally, the user should specify the cost for positive adjustments, but for now we stick to the logic of "using system cost"
                    
                    $totalValue = ($currentQtyBal * $currentAvgBal) + ($adjustmentQty * $avgCost);
                    $totalQty = $currentQtyBal + $adjustmentQty;
                    
                    $balance->quantity = $totalQty;
                    if ($totalQty > 0) {
                        $balance->average_unit_cost = $totalValue / $totalQty;
                    }
                } else {
                    // LOSS: Treat as usage (keep WAC, reduce Qty)
                    $balance->quantity += $adjustmentQty; // adjustmentQty is negative
                }
                
                $balance->last_transaction_id = $transaction->id;
                $balance->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error saving adjustment: ' . $e->getMessage());
        }

        return redirect()->route('stock-adjustments.show', $adjustment)
                         ->with('success', 'Stock Adjustment posted successfully.');
    }

    /**
     * Display the specified resource. (The Detailed View)
     */
    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load('location', 'user', 'items.item');
        
        return view('stock-adjustments.show', compact('stockAdjustment'));
    }
}