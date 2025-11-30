<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Client;
use App\Models\Billing;
use App\Models\PurchaseOrder;
use App\Models\MaterialRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // === 1. Action Items (Counts & Lists) ===
        $pendingRequests = MaterialRequest::with('project.quotation', 'requester')
            ->where('status', 'pending_approval')
            ->latest()
            ->take(5)
            ->get();
        
        $latePurchaseOrders = PurchaseOrder::with('supplier')
            ->whereIn('status', ['ordered', 'partially_received'])
            ->where('expected_delivery_date', '<', now()->toDateString())
            ->get();

        // === 2. Financial KPIs ===
        $outstandingInvoices = Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->get()
            ->sum('remaining_balance');
        
        // [NEW] Trend for Outstanding Invoices (vs Last Month)
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $outstandingLastMonth = Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->whereBetween('issued_date', [$lastMonthStart, $lastMonthEnd])
            ->get()
            ->sum('remaining_balance');
        
        // Avoid division by zero
        $outstandingTrend = 0;
        if ($outstandingLastMonth > 0) {
            $outstandingTrend = (($outstandingInvoices - $outstandingLastMonth) / $outstandingLastMonth) * 100;
        }

        $totalActiveProjectValue = Project::whereIn('status', ['initiated', 'in_progress'])
                                     ->sum('total_budget');

        $procurementBacklogCount = MaterialRequest::where('status', 'approved')
                                    ->doesntHave('purchaseOrders')
                                    ->count();

        // === 3. Cash Flow Chart Data (Last 6 Months) ===
        $chartLabels = [];
        $invoicedData = [];
        $paidData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $chartLabels[] = $month->format('M Y');
            $invoicedData[] = Invoice::whereBetween('issued_date', [$monthStart, $monthEnd])->where('status', '!=', 'draft')->sum('total_amount');
            $paidData[] = Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount');
        }
        $cashFlowChartData = [
            'labels' => $chartLabels,
            'datasets' => [
                ['label' => 'Invoiced', 'data' => $invoicedData, 'backgroundColor' => 'rgba(79, 70, 229, 0.8)'],
                ['label' => 'Paid', 'data' => $paidData, 'backgroundColor' => 'rgba(5, 150, 105, 0.7)']
            ]
        ];

        // === 4. Project Health KPIs (Complex Calculations) ===
        $activeProjects = Project::with([
                'quotation.allItems.progressUpdates', // Eager load all WBS items and their progress
                'quotation.allItems.progressUpdates.materialUsages', 
                'quotation.allItems.progressUpdates.laborUsages', 
                'quotation.allItems.progressUpdates.equipmentUsages',
                'client'
            ])
            ->whereIn('status', ['initiated', 'in_progress'])
            ->get();
        
        $activeProjectsCount = $activeProjects->count();
        $projectsOnTrackCount = 0;
        
        $totalCostVariance = 0;
        $totalEarnedValue = 0;
        $totalActualCost = 0;
        $costVarianceChartData = ['labels' => [], 'data' => []];

        // Risk Counters
        $projectsOverBudgetCount = 0;
        $projectsDelayedCount = 0;

        foreach ($activeProjects as $project) {
            
            // [FIXED] Calculate Actual Progress using the CORRECT weighted average
            $project->actual_progress = $this->getWbsActualProgress($project);
            
            // Calculate Planned Progress
            $project->planned_progress = $this->getWbsPlannedProgress($project);

            // Check Schedule
            if ($project->actual_progress >= $project->planned_progress - 0.01) {
                $projectsOnTrackCount++;
            } else {
                $projectsDelayedCount++;
            }
            
            // Calculate EV, AC, and CV for each project
            $project->actual_cost = $project->quotation->allItems->sum('actual_cost'); // Summing accessor is fine
            $project->earned_value = (float)$project->total_budget * ($project->actual_progress / 100);
            $project->cost_variance = $project->earned_value - $project->actual_cost;

            if ($project->cost_variance < 0) {
                $projectsOverBudgetCount++;
            }

            // Add to totals
            $totalEarnedValue += $project->earned_value;
            $totalActualCost += $project->actual_cost;
            $totalCostVariance += $project->cost_variance;
            
            // Add data for the new chart
            $costVarianceChartData['labels'][] = $project->project_code;
            $costVarianceChartData['data'][] = $project->cost_variance;
        }

        $totalCpi = ($totalActualCost > 0) ? $totalEarnedValue / $totalActualCost : 1;

        // [NEW] Top 5 Projects by Profit (CV)
        $topProjectsByProfit = $activeProjects->sortByDesc('cost_variance')->take(5);
        
        // [NEW] Top 5 Projects by Loss (CV) - actually bottom 5
        $topProjectsByLoss = $activeProjects->sortBy('cost_variance')->take(5);

        // [NEW] Overdue Invoices Count/Sum
        $overdueInvoicesCount = Invoice::where('status', 'overdue')->count();
        // FIX: remaining_balance is an accessor, so we must fetch the collection first
        $overdueInvoicesSum = Invoice::where('status', 'overdue')->get()->sum('remaining_balance');

        // === 5. Net Profit Trend (Historical CV) ===
        $lastMonthDate = now()->subDays(30);
        $totalCostVarianceLastMonth = 0;

        // We need to calculate CV for EACH active project as it was 30 days ago
        // This is an approximation assuming the same projects were active. 
        // For higher accuracy, we should check project status history, but this is acceptable for a trend indicator.
        foreach ($activeProjects as $project) {
            $actualProgressLastMonth = $this->getWbsActualProgressAtDate($project, $lastMonthDate);
            $earnedValueLastMonth = (float)$project->total_budget * ($actualProgressLastMonth / 100);
            $actualCostLastMonth = $this->getProjectActualCostAtDate($project, $lastMonthDate);
            
            $cvLastMonth = $earnedValueLastMonth - $actualCostLastMonth;
            $totalCostVarianceLastMonth += $cvLastMonth;
        }

        $netProfitTrend = 0;
        // Avoid division by zero and handle sign flips
        if ($totalCostVarianceLastMonth != 0) {
            $netProfitTrend = (($totalCostVariance - $totalCostVarianceLastMonth) / abs($totalCostVarianceLastMonth)) * 100;
        } elseif ($totalCostVariance != 0) {
            $netProfitTrend = 100; // From 0 to something is 100% growth
        }

        return view('dashboard', [
            'activeProjectsCount' => $activeProjectsCount,
            'projectsOnTrackCount' => $projectsOnTrackCount,
            
            'totalCostVariance' => $totalCostVariance,
            'totalCpi' => $totalCpi,

            'outstandingInvoices' => $outstandingInvoices,
            'outstandingTrend' => $outstandingTrend,
            
            'totalActiveProjectValue' => $totalActiveProjectValue,
            'procurementBacklogCount' => $procurementBacklogCount,

            'cashFlowChartData' => $cashFlowChartData,
            'costVarianceChartData' => $costVarianceChartData,
            
            'pendingRequests' => $pendingRequests,
            'latePurchaseOrders' => $latePurchaseOrders,
            'activeProjects' => $activeProjects->take(5), // Limit table to top 5
            
            // New Variables
            'projectsOverBudgetCount' => $projectsOverBudgetCount,
            'projectsDelayedCount' => $projectsDelayedCount,
            'topProjectsByProfit' => $topProjectsByProfit,
            'topProjectsByLoss' => $topProjectsByLoss,
            'overdueInvoicesCount' => $overdueInvoicesCount,
            'overdueInvoicesSum' => $overdueInvoicesSum,
            'netProfitTrend' => $netProfitTrend,
        ]);
    }

    // [NEW] Helper to get Actual Progress at a specific date
    private function getWbsActualProgressAtDate($project, $date): float
    {
        $totalBudget = (float) $project->total_budget;
        if ($totalBudget == 0) return 0;

        $tasks = $project->quotation->allItems->filter(fn($item) => $item->children->isEmpty());
        $totalEarnedValue = 0;

        foreach ($tasks as $task) {
            $taskWeight = (float)$task->subtotal;
            
            // Find the latest progress update ON or BEFORE the date
            $latestUpdate = $task->progressUpdates
                ->where('date', '<=', $date)
                ->sortByDesc('date')
                ->first();

            $taskProgress = $latestUpdate ? (float)$latestUpdate->percent_complete : 0;
            $totalEarnedValue += $taskWeight * ($taskProgress / 100);
        }

        return round(($totalEarnedValue / $totalBudget) * 100, 2);
    }

    // [NEW] Helper to get Actual Cost at a specific date
    private function getProjectActualCostAtDate($project, $date): float
    {
        $totalActualCost = 0;
        $tasks = $project->quotation->allItems->filter(fn($item) => $item->children->isEmpty());

        foreach ($tasks as $task) {
            // Filter updates on or before date
            $updates = $task->progressUpdates->where('date', '<=', $date);
            
            foreach ($updates as $update) {
                // Sum usages for this update
                $materialCost = $update->materialUsages->sum(fn($u) => $u->quantity_used * $u->unit_cost);
                $laborCost = $update->laborUsages->sum(fn($u) => $u->quantity_used * $u->unit_cost);
                $equipmentCost = $update->equipmentUsages->sum('total_cost');
                
                $totalActualCost += ($materialCost + $laborCost + $equipmentCost);
            }
        }

        return $totalActualCost;
    }
    /**
     * [NEW] Calculates the "Akumulasi Actual" using a budget-weighted average.
     */
    private function getWbsActualProgress(Project $project): float
    {
        $totalBudget = (float) $project->total_budget;
        if ($totalBudget == 0) {
            return 0;
        }

        $tasks = $project->quotation->allItems->filter(fn($item) => $item->children->isEmpty());
        $totalEarnedValue = 0;

        foreach ($tasks as $task) {
            $taskWeight = (float)$task->subtotal; // The budget of the task
            $taskProgress = (float)$task->latest_progress; // The actual % complete
            $totalEarnedValue += $taskWeight * ($taskProgress / 100);
        }

        return round(($totalEarnedValue / $totalBudget) * 100, 2);
    }

    /**
     * Calculates the "Akumulasi Rencana" based on the WBS schedule.
     */
    private function getWbsPlannedProgress(Project $project): float
    {
        $totalBudget = (float) $project->total_budget;
        if ($totalBudget == 0) {
            return 0;
        }

        $project->loadMissing('quotation.allItems'); // Ensure allItems are loaded
        $tasks = $project->quotation->allItems->filter(fn($item) => $item->children->isEmpty());
        $today = Carbon::today();
        $totalPlannedValue = 0;

        foreach ($tasks as $task) {
            $taskWeight = (float)$task->subtotal;
            $planned_start = $task->planned_start ? Carbon::parse($task->planned_start) : null;
            $planned_end = $task->planned_end ? Carbon::parse($task->planned_end) : null;

            if ($planned_start && $planned_end && $planned_start <= $today) {
                if ($today >= $planned_end) {
                    $totalPlannedValue += $taskWeight;
                } else {
                    $totalDuration = $planned_start->diffInDays($planned_end) + 1;
                    $elapsedDuration = $planned_start->diffInDays($today) + 1;
                    
                    if ($totalDuration <= 0) {
                         $totalPlannedValue += $taskWeight;
                    } else {
                        $taskPlannedProgress = ($elapsedDuration / $totalDuration);
                        $totalPlannedValue += $taskWeight * $taskPlannedProgress;
                    }
                }
            }
        }
        
        return round(($totalPlannedValue / $totalBudget) * 100, 2);
    }
}