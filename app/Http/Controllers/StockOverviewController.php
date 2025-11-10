<?php

namespace App\Http\Controllers;

use App\Models\StockLocation;
use App\Models\InventoryItem;
use App\Models\GoodsReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOverviewController extends Controller
{
    /**
     * Display a list of all stock locations to choose from.
     */
    public function index()
    {
        $locations = StockLocation::with('project.quotation')
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
            
        return view('stock-overview.index', compact('locations'));
    }

    /**
     * Show the detailed stock report for a single location.
     */
    public function show(StockLocation $stockLocation)
    {
        // 1. Get ON HAND quantities for this location
        $onHand = DB::table('stock_transactions')
            ->select('inventory_item_id', DB::raw('SUM(quantity) as on_hand_qty'))
            ->where('stock_location_id', $stockLocation->id)
            ->groupBy('inventory_item_id')
           // ->having('on_hand_qty', '!=', 0); // for sql
           ->having(DB::raw('SUM(quantity)'), '!=', 0);

        // 2. Get ON ORDER quantities for this location
        // This is defined as items on DRAFT Goods Receipts linked to this location
        $onOrder = DB::table('goods_receipts')
            ->join('purchase_orders', 'goods_receipts.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->select('purchase_order_items.inventory_item_id', DB::raw('SUM(purchase_order_items.quantity_ordered - purchase_order_items.quantity_received) as on_order_qty'))
            ->where('goods_receipts.stock_location_id', $stockLocation->id)
            ->where('goods_receipts.status', 'draft') // Only DRAFT receipts count as "on order"
            ->groupBy('purchase_order_items.inventory_item_id')
            //->having('on_order_qty', '>', 0);
            ->having(DB::raw('SUM(purchase_order_items.quantity_ordered - purchase_order_items.quantity_received)'), '>', 0);
        
        // 3. Get all inventory items involved
        $inventoryItemIds = $onHand->pluck('inventory_item_id')
                            ->merge($onOrder->pluck('inventory_item_id'))
                            ->unique();
        
        $items = InventoryItem::whereIn('id', $inventoryItemIds)
                    ->orderBy('item_code')
                    ->get()
                    ->keyBy('id');

        // 4. Get the data and key it by item ID for the view
        $onHandStock = $onHand->get()->keyBy('inventory_item_id');
        $onOrderStock = $onOrder->get()->keyBy('inventory_item_id');
        
        // 5. Build the final report array
        $reportData = collect();
        foreach($items as $id => $item) {
            $reportData->push([
                'item' => $item,
                'on_hand' => $onHandStock->get($id)->on_hand_qty ?? 0,
                'on_order' => $onOrderStock->get($id)->on_order_qty ?? 0,
                // You can add 'forecasted' here later
            ]);
        }

        return view('stock-overview.show', [
            'location' => $stockLocation,
            'reportData' => $reportData
        ]);
    }
}