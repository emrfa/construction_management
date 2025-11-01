<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\Billing;
use App\Models\PurchaseOrder;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        // 1. KPI Stats
        $activeProjectsCount = Project::whereIn('status', ['initiated', 'in_progress'])->count();
        $clientCount = Client::count();
        $pendingBillingsCount = Billing::where('status', 'pending')->count();
        $pendingRequestsCount = MaterialRequest::where('status', 'pending_approval')->count();

        // 2. Actionable Lists
        $pendingRequests = MaterialRequest::with('project', 'requester')
            ->where('status', 'pending_approval')
            ->latest()
            ->take(5)
            ->get();
            
        $pendingBillings = Billing::with('project')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // 3. Project Overview
        $activeProjects = Project::with('client')
            ->whereIn('status', ['initiated', 'in_progress'])
            ->latest('start_date')
            ->take(10)
            ->get();

        return view('dashboard', [
            'activeProjectsCount' => $activeProjectsCount,
            'clientCount' => $clientCount,
            'pendingBillingsCount' => $pendingBillingsCount,
            'pendingRequestsCount' => $pendingRequestsCount,
            'pendingRequests' => $pendingRequests,
            'pendingBillings' => $pendingBillings,
            'activeProjects' => $activeProjects,
        ]);
    }
}