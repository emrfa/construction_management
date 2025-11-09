<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialUsage;
use App\Models\InventoryItem;
use App\Models\StockTransaction;

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
            'quotation.allItems.unitRateAnalysis', // Load AHS for budget
            'quotation.allItems.progressUpdates.materialUsages', // Load material costs
            'quotation.allItems.progressUpdates.laborUsages', // Load labor
            'quotation.allItems.progressUpdates.equipmentUsages', // Load equipment
        ]);

        $projectBudget = (float) $project->total_budget;
        if ($projectBudget == 0) {
            return back()->with('error', 'Cannot generate report. Project budget is zero.');
        }

        $tasks = $project->quotation->allItems->filter(fn($item) => $item->children->isEmpty());
        $today = Carbon::today();

        // --- 2. DEFINE PROJECT TIME PERIOD ---
        $projectStartDate = $project->start_date ? $project->start_date : $tasks->min('planned_start');
        $projectEndDate = $project->end_date ? $project->end_date : $tasks->max('planned_end');

        if (!$projectStartDate || !$projectEndDate) {
            return back()->with('error', 'Cannot generate S-Curve. Please set Project Start/End dates or schedule tasks in the Project Scheduler.');
        }

        // --- NEW: Smart Interval Logic ---
        $totalDurationInDays = $projectStartDate->diffInDays($projectEndDate);
        $intervalSpec = '1 week'; // Default
        
        if ($totalDurationInDays <= 90) { // 3 months or less
            $intervalSpec = '1 day';
        } elseif ($totalDurationInDays > 540) { // ~1.5 years or more
            $intervalSpec = '1 month';
        }
        // Else, it stays '1 week' (for durations between ~3 months and 1.5 years)
        // --- END NEW: Smart Interval Logic ---


        // --- MODIFIED: Create the period.
        $periodObject = CarbonPeriod::create($projectStartDate, $intervalSpec, $projectEndDate);
        
        // --- THIS IS THE FIX ---
        $period = $periodObject->toArray(); // Convert to array
        $lastPeriodDate = end($period);
        if ($lastPeriodDate && !$lastPeriodDate->isSameDay($projectEndDate)) {
            // Add the true project end date to the loop to ensure 100% is reached
            $period[] = $projectEndDate;
        }
        // --- END FIX ---


        // --- 3. CALCULATE CUMULATIVE S-CURVE DATA ---
        $chartLabels = [];
        $chartData = [
            'pv_currency' => [], // Planned Value (Currency)
            'ev_currency' => [], // Earned Value (Currency)
            'ac_currency' => [], // Actual Cost (Currency)
            'pv_percent' => [], // Planned Value (%)
            'ev_percent' => [], // Earned Value (%)
        ];
        $cumulativeAC = 0;

        // --- 4. PREPARE NEW TASK-LEVEL DETAIL ARRAY ---
        $taskDetails = [];
        foreach ($tasks as $task) {
            $taskWeight = ((float)$task->subtotal / $projectBudget) * 100;
            $taskDetails[$task->id] = [
                'id' => $task->id,
                'name' => $task->description, // Matches 'name' in your blade
                'weight' => $taskWeight,
                'budget' => (float)$task->subtotal,
                'weekly_planned' => [], // Per-period PV (%)
                'weekly_actual' => [],  // Per-period EV (%)
                
                // --- NEW: Data for task S-Curves ---
                'cumulative_pv_currency' => [], 
                'cumulative_ev_currency' => [], 
                'cumulative_ac_currency' => [], 
                
                'total_planned_percent' => 0, // Last known planned %
                'total_actual_percent' => 0,  // Last known actual %
                'actual_costs_by_date' => [], // Temp storage for task AC
            ];
        }

        // --- 5. LOOP THROUGH TIME PERIOD TO CALCULATE ALL DATA ---

        // Pre-calculate time-phased Actual Costs for project and tasks
        $actualCostsByDate = new Collection();
        foreach ($tasks as $task) {
            $taskAcByDate = [];
            foreach ($task->progressUpdates as $update) {
                $materialCost = $update->materialUsages->sum(fn($usage) => $usage->quantity_used * $usage->unit_cost);
                $laborCost = $update->laborUsages->sum(fn($usage) => $usage->quantity_used * $usage->unit_cost);
                $equipmentCost = $update->equipmentUsages->sum('total_cost');

                $dailyCost = $materialCost + $laborCost + $equipmentCost;
                if ($dailyCost == 0) continue;

                // --- FIX 1: Convert the Carbon object to a string key ---
                $acDate = $update->date->format('Y-m-d');

                // Add to overall project AC
                $actualCostsByDate[$acDate] = ($actualCostsByDate[$acDate] ?? 0) + $dailyCost;

                // --- NEW: Add to specific task AC
                $taskAcByDate[$acDate] = ($taskAcByDate[$acDate] ?? 0) + $dailyCost;
            }
            $taskDetails[$task->id]['actual_costs_by_date'] = $taskAcByDate; // Store task ACs
        }

        // Sort AC dates for processing
        $sortedAcDates = $actualCostsByDate->sortKeys();
        
        $cumulativePV_Percent = 0;
        $cumulativeEV_Percent = 0;

        // Main loop over the calculated time period
        foreach ($period as $date) { // $period is now the corrected array
            $currentDate = $date->copy();
            $chartLabels[] = $currentDate->format('d-M-y'); // This is your new "week_labels"

            $periodPV_Percent = 0; // This period's PV (%)
            $periodEV_Percent = 0; // This period's EV (%)
            $periodAC_Currency = 0; // This period's AC (Currency)

            // --- Calculate Actual Cost (AC) for this period (Project Level) ---
            foreach ($sortedAcDates as $acDate => $cost) {
                if (Carbon::parse($acDate) <= $currentDate) {
                    $periodAC_Currency += $cost;
                    $sortedAcDates->forget($acDate); // Remove, so it's only counted once
                }
            }
            $cumulativeAC += $periodAC_Currency;

            // --- Calculate PV and EV for each task ---
            foreach ($tasks as $task) {
                $taskWeight = $taskDetails[$task->id]['weight'];
                $planned_start = $task->planned_start ? $task->planned_start : null;
                $planned_end = $task->planned_end ? $task->planned_end : null;
                $currentPeriodPlannedWeight = 0;
                $currentPeriodActualWeight = 0;

                // --- Calculate Planned Value (PV) ---
                if ($planned_start && $planned_end && $planned_start <= $currentDate) {
                    if ($currentDate >= $planned_end) {
                        $currentPeriodPlannedWeight = $taskWeight; // Full weight
                    } else {
                        $totalDuration = $planned_start->diffInDays($planned_end) + 1;
                        $elapsedDuration = $planned_start->diffInDays($currentDate) + 1;
                        $currentPeriodPlannedWeight = ($elapsedDuration / $totalDuration) * $taskWeight;
                    }
                }
                
                $taskData = $taskDetails[$task->id]; // Get the item
                $lastPeriodPlannedPercent = $taskData['total_planned_percent']; // Get last known total %
                $taskData['weekly_planned'][] = max(0, $currentPeriodPlannedWeight - $lastPeriodPlannedPercent); // Store delta %
                $taskData['total_planned_percent'] = $currentPeriodPlannedWeight; // Update total %
                
                // --- Calculate Earned Value (EV) ---
                $latestUpdate = $task->progressUpdates
                    ->where('date', '<=', $currentDate)
                    ->sortByDesc('date')
                    ->first();

                $currentPeriodActualPercent = $latestUpdate ? $latestUpdate->percent_complete : 0;
                $currentPeriodActualWeight = ($currentPeriodActualPercent / 100) * $taskWeight;

                $lastPeriodActualPercent = $taskData['total_actual_percent']; // Get last known total %
                $taskData['weekly_actual'][] = max(0, $currentPeriodActualWeight - $lastPeriodActualPercent); // Store delta %
                $taskData['total_actual_percent'] = $currentPeriodActualWeight; // Update total %

                // --- NEW: Calculate Task-level AC for this period ---
                $cumulativeTaskAC = $taskData['cumulative_ac_currency'] ? end($taskData['cumulative_ac_currency']) : 0;
                $periodTaskAC = 0;

                $taskAcDates = collect($taskData['actual_costs_by_date']);
                foreach ($taskAcDates as $acDate => $cost) {
                    // --- FIX 2: Check the parsed date string ---
                    if (Carbon::parse($acDate) <= $currentDate) {
                        $periodTaskAC += $cost;
                        $taskAcDates->forget($acDate); 
                    }
                }
                $taskData['actual_costs_by_date'] = $taskAcDates->all(); // Persist changes
                $cumulativeTaskAC += $periodTaskAC;
                // --- END NEW: Task-level AC ---


                // --- NEW: Store cumulative data for task charts (in Currency) ---
                $taskData['cumulative_pv_currency'][] = ($currentPeriodPlannedWeight / 100) * $projectBudget;
                $taskData['cumulative_ev_currency'][] = ($currentPeriodActualWeight / 100) * $projectBudget;
                $taskData['cumulative_ac_currency'][] = $cumulativeTaskAC;
                // --- END NEW ---
                
                $taskDetails[$task->id] = $taskData; // Put it back

                // Add this task's progress to the overall project totals
                $periodPV_Percent += $currentPeriodPlannedWeight;
                $periodEV_Percent += $currentPeriodActualWeight;
            } // End foreach $tasks

            // Store cumulative PROJECT totals for the main chart
            $cumulativePV_Percent = $periodPV_Percent;
            $cumulativeEV_Percent = $periodEV_Percent;

            // Store data for main chart (Currency)
            $chartData['pv_currency'][] = ($cumulativePV_Percent / 100) * $projectBudget;
            $chartData['ev_currency'][] = ($cumulativeEV_Percent / 100) * $projectBudget;
            $chartData['ac_currency'][] = $cumulativeAC;
            
            // Store data for footer rows (%)
            $chartData['pv_percent'][] = $cumulativePV_Percent;
            $chartData['ev_percent'][] = $cumulativeEV_Percent;

        } // End foreach $period

        // --- 6. CALCULATE FINAL KPI VALUES (as of TODAY) ---
        $currentPV_Currency = 0;
        $currentEV_Currency = 0;
        $currentAC_Currency = 0;
        $currentPV_Percent = 0;
        $currentEV_Percent = 0;
        
        $todayIndex = -1;

        foreach ($chartLabels as $index => $label) {
            if (Carbon::parse($label) >= $today) {
                $todayIndex = $index;
                break;
            }
        }

        if ($todayIndex != -1) {
            $currentPV_Currency = $chartData['pv_currency'][$todayIndex];
            $currentEV_Currency = $chartData['ev_currency'][$todayIndex];
            $currentAC_Currency = $chartData['ac_currency'][$todayIndex];
            $currentPV_Percent = $chartData['pv_percent'][$todayIndex];
            $currentEV_Percent = $chartData['ev_percent'][$todayIndex];
        } else { // Project is complete, use last known values
            $currentPV_Currency = end($chartData['pv_currency']);
            $currentEV_Currency = end($chartData['ev_currency']);
            $currentAC_Currency = end($chartData['ac_currency']);
            $currentPV_Percent = end($chartData['pv_percent']);
            $currentEV_Percent = end($chartData['ev_percent']);
        }
        
        // --- 7. PREPARE DATA FOR THE VIEW ---
        // This array structure matches your old Blade file
        $reportData = [
            'cost_variance' => $currentEV_Currency - $currentAC_Currency,
            'schedule_variance' => $currentEV_Currency - $currentPV_Currency,
            'planned_percent' => $currentPV_Percent,
            'earned_percent' => $currentEV_Percent,
            
            // Data for main chart
            'chart_data' => [
                'labels' => $chartLabels,
                'planned' => $chartData['pv_currency'],
                'earned' => $chartData['ev_currency'],
                'actual' => $chartData['ac_currency'],
            ],
            
            // Data for task table headers
            'week_labels' => $chartLabels, // Use the new smart labels
            
            // Data for task table body
            'task_details' => $taskDetails, // This now contains all data
            
            // Data for table footer
            'footer_planned_percent' => $chartData['pv_percent'],
            'footer_actual_percent' => $chartData['ev_percent'],
        ];

        return view('reports.project_performance', [
            'project' => $project,
            'reportData' => $reportData,
            
            // We pass this one extra variable for the *new* task chart JS
            'taskScurveData' => $taskDetails 
        ]);
    }

    /**
     * Show a report of all stock, grouped by item and location.
     */
    public function stockBalanceReport()
    {
        $stockBalances = StockTransaction::with([
                'item', // Eager-load the item details
                'stockLocation.project.quotation' // Eager-load the location, and *if* it's a project, its details
            ])
            ->select('inventory_item_id', 'stock_location_id', DB::raw('SUM(quantity) as on_hand')) // <-- Use stock_location_id
            ->groupBy('inventory_item_id', 'stock_location_id') // <-- Use stock_location_id
            ->having('on_hand', '!=', 0) // Only show items with stock
            ->get();

        // Sort the results by item code, then by location name
        $sortedBalances = $stockBalances->sortBy([
            ['item.item_code', 'asc'],
            ['stockLocation.name', 'asc']
        ]);

        return view('reports.stock_balance', ['balances' => $sortedBalances]);
    }
}
