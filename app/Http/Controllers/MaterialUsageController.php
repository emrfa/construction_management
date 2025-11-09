<?php

namespace App\Http\Controllers;

use App\Models\MaterialUsage;
use App\Models\Project;
use Illuminate\Http\Request;

class MaterialUsageController extends Controller
{
    /**
     * Display a listing of all material usages.
     */
    public function index(Request $request)
    {
        $projects = Project::with('quotation')->get();
        
        $query = MaterialUsage::with([
            'inventoryItem',
            'progressUpdate.quotationItem.quotation.project',
            'progressUpdate.stockTransactions.stockLocation' // Get the location from the related transaction
        ])->latest('id'); // Order by most recent usage

        // Filter by Project
        if ($request->filled('project_id')) {
            $query->whereHas('progressUpdate.quotationItem.quotation.project', function ($q) use ($request) {
                $q->where('id', $request->project_id);
            });
        }

        $usages = $query->paginate(30)->withQueryString();

        return view('material-usage.index', compact('usages', 'projects'));
    }
}