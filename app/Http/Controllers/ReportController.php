<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialUsage;
use App\Models\InventoryItem;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function materialFlowReport(Project $project)
    {
        // --- Data Processing Logic ---
        $reportData = collect();

        // 1. Get all unique Inventory Item IDs involved for this project
        $requestedItemIds = MaterialRequestItem::whereHas('materialRequest', fn($q) => $q->where('project_id', $project->id))
            ->pluck('inventory_item_id');

        $poItemIds = PurchaseOrderItem::whereHas('purchaseOrder', fn($q) => $q->where('project_id', $project->id))
            ->pluck('inventory_item_id');

        // Usage IDs: Need to join through progress updates and quotation items
        $usageItemIds = MaterialUsage::whereHas('progressUpdate.quotationItem.quotation', fn($q) => $q->where('id', $project->quotation_id))
            ->pluck('inventory_item_id');

        // Combine and get unique IDs
        $allItemIds = $requestedItemIds->merge($poItemIds)->merge($usageItemIds)->unique();

        // 2. Fetch Item Details and Initialize Report Structure
        $inventoryItems = InventoryItem::whereIn('id', $allItemIds)->get()->keyBy('id');

        foreach ($allItemIds as $itemId) {
            $item = $inventoryItems->get($itemId);
            if ($item) {
                $reportData->put($itemId, [
                    'item_id'       => $itemId,
                    'item_code'     => $item->item_code,
                    'item_name'     => $item->item_name,
                    'uom'           => $item->uom,
                    'requested_qty' => 0.0,
                    'ordered_qty'   => 0.0,
                    'received_qty'  => 0.0,
                    'used_qty'      => 0.0,
                ]);
            }
        }

        // 3. Aggregate Requested Quantities
        $requestedTotals = MaterialRequestItem::whereHas('materialRequest', fn($q) => $q->where('project_id', $project->id))
            ->select('inventory_item_id', DB::raw('SUM(quantity_requested) as total_requested'))
            ->groupBy('inventory_item_id')
            ->pluck('total_requested', 'inventory_item_id'); // Keyed by item ID

        foreach ($requestedTotals as $itemId => $total) {
            if ($reportData->has($itemId)) {
                $currentItem = $reportData->get($itemId);
                $currentItem['requested_qty'] = (float)$total;
                $reportData->put($itemId, $currentItem);
            }
        }

        // 4. Aggregate Ordered & Received Quantities
        $poTotals = PurchaseOrderItem::whereHas('purchaseOrder', fn($q) => $q->where('project_id', $project->id))
            ->select(
                'inventory_item_id',
                DB::raw('SUM(quantity_ordered) as total_ordered'),
                DB::raw('SUM(quantity_received) as total_received')
            )
            ->groupBy('inventory_item_id')
            ->get() // Get collection of objects
            ->keyBy('inventory_item_id'); // Key by item ID

        foreach ($poTotals as $itemId => $totals) {
            if ($reportData->has($itemId)) {
                $currentItem = $reportData->get($itemId);
                $currentItem['ordered_qty'] = (float)$totals->total_ordered;
                $currentItem['received_qty'] = (float)$totals->total_received;
                $reportData->put($itemId, $currentItem);
            }
        }

        // 5. Aggregate Used Quantities
        $usageTotals = MaterialUsage::whereHas('progressUpdate.quotationItem.quotation', fn($q) => $q->where('id', $project->quotation_id))
            ->select('inventory_item_id', DB::raw('SUM(quantity_used) as total_used'))
            ->groupBy('inventory_item_id')
            ->pluck('total_used', 'inventory_item_id'); // Keyed by item ID

        foreach ($usageTotals as $itemId => $total) {
             if ($reportData->has($itemId)) {
                $currentItem = $reportData->get($itemId);
                $currentItem['used_qty'] = (float)$total;
                $reportData->put($itemId, $currentItem);
            }
        }

        // --- Sort and Pass data to the view ---
        $sortedReportData = $reportData->sortBy('item_code')->values(); // Get simple indexed array

        return view('reports.material_flow', [
            'project' => $project,
            'reportData' => $sortedReportData // Pass the final sorted data
        ]);
    }

    /**
     * Display the Project Performance report (RAB vs Progress vs Cost)
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function projectPerformanceReport(Project $project)
    {
        // --- Data Calculation ---

        // 1. Budget (RAB)
        $budgetTotal = (float) $project->total_budget;

        // 2. Physical Progress (%) & Earned Value (EV)
        // We need to calculate the overall project physical progress based on WBS items
        $project->loadMissing('quotation.items'); // Load root WBS items
        $physicalProgressPercent = 0.0;
        $earnedValue = 0.0;

        if ($budgetTotal > 0 && $project->quotation) {
            $weightedProgressSum = 0;
            // Iterate through root items; latest_progress accessor handles recursion
            foreach ($project->quotation->items as $rootItem) {
                $itemBudget = (float)($rootItem->subtotal ?? 0);
                $itemProgress = $rootItem->latest_progress; // This uses the accessor
                $weightedProgressSum += $itemBudget * ($itemProgress / 100);
            }
            // Overall Physical Progress % = Weighted Sum / Total Budget * 100
            $physicalProgressPercent = round(($weightedProgressSum / $budgetTotal) * 100, 1);
            // Earned Value = Total Budget * Overall Progress %
            $earnedValue = $budgetTotal * ($physicalProgressPercent / 100);
        }


        // 3. Actual Cost (AC - Based on Usage)
        // Use the existing accessor on the Project model
        $actualCost = $project->actual_cost; // This triggers the getActualCostAttribute() method


        // 4. Procurement Progress (Value Received)
        // Sum (quantity_received * unit_cost) for all PO items linked to this project
        $procurementValueReceived = PurchaseOrderItem::whereHas('purchaseOrder', fn($q) => $q->where('project_id', $project->id))
            ->sum(DB::raw('quantity_received * unit_cost'));


        // 5. Cost Variance (CV = EV - AC)
        $costVariance = $earnedValue - $actualCost;

        // Note: Schedule Variance (SV = EV - PV) requires Planned Value (PV) calculation,
        // which needs time-phased budget data (which we don't have yet).


        // --- Prepare Data for View ---
        $reportData = [
            'budget_total' => $budgetTotal,
            'physical_progress_percent' => $physicalProgressPercent,
            'earned_value' => $earnedValue,
            'actual_cost' => $actualCost,
            'procurement_value_received' => (float)$procurementValueReceived, // Cast to float
            'cost_variance' => $costVariance,
        ];


        // --- Pass data to the view ---
        return view('reports.project_performance', [
            'project' => $project,
            'reportData' => $reportData // Pass the processed data
        ]);
    }
}
