<?php

namespace App\Http\Controllers;

use App\Models\StockLocation;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start query
        $query = StockLocation::with('project.quotation')
            ->orderBy('type')
            ->orderBy('name');
            
        // **NEW**: Apply search logic
        $query->when($request->search, function ($q, $search) {
            return $q->where('code', 'like', "%{$search}%")
                     ->orWhere('name', 'like', "%{$search}%")
                     ->orWhereHas('project', function ($subQuery) use ($search) {
                         $subQuery->where('project_code', 'like', "%{$search}%");
                     });
        });
            
        // [MODIFIED] Paginate the query
        $locations = $query->paginate(15)->appends($request->query());
            
        return view('stock-locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Find projects that DON'T already have a location
        $projects = Project::whereDoesntHave('stockLocation')
            ->with('quotation')
            ->get();
            
        return view('stock-locations.create', compact('projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:stock_locations,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'type' => 'required|in:warehouse,site',
            'project_id' => [
                'nullable',
                'required_if:type,site', // Project is required if type is 'site'
                'exists:projects,id',
                'unique:stock_locations,project_id' // A project can only have one location
            ],
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        StockLocation::create($validated);
        
        return redirect()->route('stock-locations.index')
                         ->with('success', 'Stock location created successfully.');
    }

    /**
     * Display the specified resource.
     * (We'll just redirect to edit for simplicity)
     */
    public function show(StockLocation $stockLocation)
    {
        return redirect()->route('stock-locations.edit', $stockLocation);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockLocation $stockLocation)
    {
        // Find projects that DON'T have a location, PLUS the current one
        $projects = Project::whereDoesntHave('stockLocation')
            ->with('quotation')
            ->orWhere('id', $stockLocation->project_id) // Include the currently linked project
            ->get();
            
        return view('stock-locations.edit', compact('stockLocation', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockLocation $stockLocation)
    {
        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('stock_locations')->ignore($stockLocation->id),
            ],
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'type' => 'required|in:warehouse,site',
            'project_id' => [
                'nullable',
                'required_if:type,site',
                'exists:projects,id',
                Rule::unique('stock_locations')->ignore($stockLocation->id),
            ],
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');

        // Prevent changing a "Main Warehouse" to a "Site" if it has stock
        if ($stockLocation->type == 'warehouse' && $validated['type'] == 'site') {
            if ($stockLocation->stockTransactions()->sum('quantity') != 0) {
                 return back()->withInput()->withErrors('Cannot change a warehouse with stock into a project site. Please transfer stock first.');
            }
        }
        
        // If type is changed to 'warehouse', unlink the project
        if ($validated['type'] == 'warehouse') {
            $validated['project_id'] = null;
        }
        
        $stockLocation->update($validated);
        
        return redirect()->route('stock-locations.index')
                         ->with('success', 'Stock location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockLocation $stockLocation)
    {
        try {
            $stockLocation->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Check for foreign key constraint violation
            if ($e->errorInfo[1] == 1451 || str_contains($e->getMessage(), 'foreign key constraint')) {
                return redirect()->route('stock-locations.index')
                                 ->with('error', 'Cannot delete this location. It is in use by stock transactions or receipts.');
            }
            return redirect()->route('stock-locations.index')->with('error', 'Could not delete location: ' . $e->getMessage());
        }
        
        return redirect()->route('stock-locations.index')
                         ->with('success', 'Stock location deleted successfully.');
    }
}