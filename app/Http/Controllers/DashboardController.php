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
        $activeProjects = Project::with('quotation.allItems.progressUpdates')
            ->whereIn('status', ['initiated', 'in_progress'])
            ->get();
        
        $activeProjectsCount = $activeProjects->count();
        $projectsOnTrackCount = 0;
        
        // [NEW] Variables for new KPIs
        $totalCostVariance = 0;
        $totalEarnedValue = 0;
        $totalActualCost = 0;
        $costVarianceChartData = ['labels' => [], 'data' => []];

        foreach ($activeProjects as $project) {
            
            $project->actual_progress = $project->quotation->items->avg('latest_progress') ?? 0;
            $project->planned_progress = $this->getWbsPlannedProgress($project);

            if ($project->actual_progress >= $project->planned_progress - 0.01) {
                $projectsOnTrackCount++;
            }
            
            $project->loadMissing('quotation.allItems.progressUpdates.materialUsages', 'quotation.allItems.progressUpdates.laborUsages', 'quotation.allItems.progressUpdates.equipmentUsages');
            
            // [NEW] Calculate EV, AC, and CV for each project
            $project->actual_cost = $project->quotation->items->sum('actual_cost');
            $project->earned_value = $project->total_budget * ($project->actual_progress / 100);
            $project->cost_variance = $project->earned_value - $project->actual_cost;

            // Add to totals
            $totalEarnedValue += $project->earned_value;
            $totalActualCost += $project->actual_cost;
            $totalCostVariance += $project->cost_variance;
            
            // Add data for the new chart
            $costVarianceChartData['labels'][] = $project->project_code;
            $costVarianceChartData['data'][] = $project->cost_variance;
        }

        // [NEW] Calculate final Cost Performance Index (CPI)
        $totalCpi = ($totalActualCost > 0) ? $totalEarnedValue / $totalActualCost : 1;

        return view('dashboard', [
            'activeProjectsCount' => $activeProjectsCount,
            'projectsOnTrackCount' => $projectsOnTrackCount,
            
            // [NEW] Pass new KPIs to the view
            'totalCostVariance' => $totalCostVariance,
            'totalCpi' => $totalCpi,

            'outstandingInvoices' => $outstandingInvoices,
            'totalActiveProjectValue' => $totalActiveProjectValue,
            'procurementBacklogCount' => $procurementBacklogCount,

            'cashFlowChartData' => $cashFlowChartData,
            'costVarianceChartData' => $costVarianceChartData, // [NEW] Pass chart data
            
            'pendingRequests' => $pendingRequests,
            'latePurchaseOrders' => $latePurchaseOrders,
            'activeProjects' => $activeProjects, // This now contains EV, AC, and CV
        ]);
    }

    /**
     * Calculates the expected project completion percentage based on the WBS schedule.
     */
    private function getWbsPlannedProgress(Project $project): float
    {
        $totalBudget = (float) $project->total_budget;
        if ($totalBudget == 0) {
            return 0;
        }

        $project->loadMissing('quotation.allItems');
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