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
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
        // --- 1. SETUP ---
        $project->load([
            'quotation.allItems.progressUpdates.materialUsages',
            'quotation.allItems.progressUpdates.laborUsages', // Load labor
            'quotation.allItems'
        ]);

        $totalBudget = $project->total_budget;
        if ($totalBudget == 0) {
            return back()->with('error', 'Cannot generate report. Project budget is zero.');
        }

        $tasks = $project->quotation->allItems->filter(fn($item) => $item->children->isEmpty());

        // --- 2. DEFINE PROJECT TIME PERIOD ---
        $projectStartDate = $project->start_date ? Carbon::parse($project->start_date) : $tasks->min('planned_start');
        $projectEndDate = $project->end_date ? Carbon::parse($project->end_date) : $tasks->max('planned_end');

        if (!$projectStartDate || !$projectEndDate) {
            return back()->with('error', 'Cannot generate S-Curve. Please set Project Start/End dates or schedule tasks in the Project Scheduler.');
        }
        
        $period = CarbonPeriod::create($projectStartDate, '1 week', $projectEndDate->addWeek());
        $today = now();
        $chartLabels = []; 

        // --- 3. CALCULATE CUMULATIVE S-CURVE DATA ---
        $cumulativePlannedData = [];
        $cumulativeEarnedData = [];
        $cumulativeActualData = [];

        // --- 4. PREPARE NEW TASK-LEVEL DETAIL ARRAY ---
        $taskDetails = new Collection();
        foreach ($tasks as $task) {
            $taskDetails[$task->id] = [
                'name' => $task->description,
                'budget' => $task->subtotal,
                'weight' => ($totalBudget > 0) ? ($task->subtotal / $totalBudget) * 100 : 0,
                'weekly_planned' => [],
                'weekly_actual' => [],
                'total_planned_percent' => 0,
                'total_actual_percent' => 0, // Note: We use lastWeekEV for this logic
            ];
        }

        // --- 5. LOOP THROUGH TIME PERIOD TO CALCULATE ALL DATA ---
        
        $cumulativeAC = 0;
        $cumulativePV = 0;
        $cumulativeEV = 0;

        $actualCostsByDate = new Collection();
        foreach ($tasks as $task) {
            foreach ($task->progressUpdates as $update) {
                $dateString = $update->date;
                $materialCost = $update->materialUsages->sum(fn($usage) => $usage->quantity_used * $usage->unit_cost);
                $laborCost = $update->laborUsages->sum(fn($usage) => $usage->quantity_used * $usage->unit_cost);
                $totalCost = $materialCost + $laborCost;

                if ($totalCost > 0) {
                    $actualCostsByDate[$dateString] = ($actualCostsByDate[$dateString] ?? 0) + $totalCost;
                }
            }
        }
        $sortedAcDates = $actualCostsByDate->sortKeys();

        $lastWeekEV = []; 

        foreach ($period as $date) {
            $currentDate = $date->copy();
            $chartLabels[] = $currentDate->format('d-M-y');
            
            $weeklyPV = 0;
            $weeklyEV = 0;

            foreach ($sortedAcDates as $acDate => $cost) {
                if (Carbon::parse($acDate) <= $currentDate) {
                    $cumulativeAC += $cost;
                    $sortedAcDates->forget($acDate);
                } else {
                    break;
                }
            }

            foreach ($tasks as $task) {
                $taskBudget = $taskDetails[$task->id]['budget'];
                $planned_start = $task->planned_start ? Carbon::parse($task->planned_start) : null;
                $planned_end = $task->planned_end ? Carbon::parse($task->planned_end) : null;
                $taskWeight = $taskDetails[$task->id]['weight'];

                // --- Calculate Planned Value (PV) ---
                $currentPlannedPercent = 0;
                if ($planned_start && $planned_end && $planned_start <= $planned_end) {
                    $totalDuration = $planned_start->diffInDays($planned_end) + 1;
                    $elapsedDuration = $planned_start->diffInDays($currentDate) + 1;

                    if ($currentDate < $planned_start) {
                        $currentPlannedPercent = 0;
                    } elseif ($currentDate >= $planned_end) {
                        $currentPlannedPercent = 100;
                    } else {
                        $currentPlannedPercent = ($elapsedDuration / $totalDuration) * 100;
                    }
                }
                
                // === START FIX FOR PLANNED ===
                $taskData = $taskDetails[$task->id]; // Get the item
                $lastWeekPlanned = $taskData['total_planned_percent'];
                $thisWeekPlannedWeight = ($currentPlannedPercent / 100) * $taskWeight;
                $taskData['weekly_planned'][] = max(0, $thisWeekPlannedWeight - $lastWeekPlanned); // Modify item
                $taskData['total_planned_percent'] = $thisWeekPlannedWeight; // Modify item
                $taskDetails[$task->id] = $taskData; // Put it back
                // === END FIX FOR PLANNED ===

                $weeklyPV += $taskBudget * ($currentPlannedPercent / 100);

                // --- Calculate Earned Value (EV) ---
                $latestUpdate = $task->progressUpdates
                    ->where('date', '<=', $currentDate)
                    ->sortByDesc('date')
                    ->first();
                
                $currentActualPercent = $latestUpdate ? $latestUpdate->percent_complete : 0;
                
                // === START FIX FOR ACTUAL ===
                $taskData = $taskDetails[$task->id]; // Get the item
                $lastWeekActual = $lastWeekEV[$task->id] ?? 0;
                $thisWeekActualWeight = ($currentActualPercent / 100) * $taskWeight;
                $taskData['weekly_actual'][] = max(0, $thisWeekActualWeight - $lastWeekActual); // Modify item
                $taskDetails[$task->id] = $taskData; // Put it back
                // === END FIX FOR ACTUAL ===
                
                $lastWeekEV[$task->id] = $thisWeekActualWeight;
                $weeklyEV += $taskBudget * ($currentActualPercent / 100);
            }

            $cumulativePlannedData[] = $weeklyPV;
            $cumulativeEarnedData[] = $weeklyEV;
            $cumulativeActualData[] = $cumulativeAC;
        }


        // --- 6. CALCULATE FINAL KPI VALUES (as of TODAY) ---
        $todayIndex = -1;
        foreach($chartLabels as $index => $label) {
            if (Carbon::parse($label) >= $today) {
                $todayIndex = $index;
                break;
            }
        }
        if ($todayIndex == -1) $todayIndex = count($chartLabels) - 1;

        $totalPlannedValue = $cumulativePlannedData[$todayIndex] ?? 0;
        $totalEarnedValue = $cumulativeEarnedData[$todayIndex] ?? 0;
        $totalActualCost = $cumulativeActualData[$todayIndex] ?? 0;

        // --- 7. PREPARE DATA FOR THE VIEW ---
        $reportData = [
            'total_budget' => $totalBudget,
            'planned_value' => $totalPlannedValue,
            'earned_value' => $totalEarnedValue,
            'actual_cost' => $totalActualCost,
            'cost_variance' => $totalEarnedValue - $totalActualCost,
            'schedule_variance' => $totalEarnedValue - $totalPlannedValue,

            'planned_percent' => ($totalBudget > 0) ? ($totalPlannedValue / $totalBudget) * 100 : 0,
            'earned_percent' => ($totalBudget > 0) ? ($totalEarnedValue / $totalBudget) * 100 : 0,

            'chart_data' => [
                'labels' => $chartLabels,
                'planned' => $cumulativePlannedData,
                'earned' => $cumulativeEarnedData,
                'actual' => $cumulativeActualData,
            ],
            
            'task_details' => $taskDetails,
            'week_labels' => $chartLabels, 
        ];

        return view('reports.project_performance', compact('project', 'reportData', 'tasks'));
    }
}
