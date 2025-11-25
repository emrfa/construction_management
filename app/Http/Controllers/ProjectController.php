<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\QuotationItem;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start query
        $query = Project::with(['client', 'quotation'])->latest();

        // **NEW**: Apply search logic
        $query->when($request->search, function ($q, $search) {
            return $q->where('project_code', 'like', "%{$search}%")
                     ->orWhereHas('quotation', function ($subQuery) use ($search) {
                         $subQuery->where('project_name', 'like', "%{$search}%");
                     })
                     ->orWhereHas('client', function ($subQuery) use ($search) {
                         $subQuery->where('name', 'like', "%{$search}%");
                     });
        });
        
        // [MODIFIED] Paginate the query
        $projects = $query->paginate(15)->appends($request->query());

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load('client', 'quotation.items.children', 'quotation.items.progressUpdates', 'billings', 'quotation', 'materialRequests', 'stockTransactions');
        $stockSummary = $project->getMaterialStockSummary();
        return view('projects.show', compact('project', 'stockSummary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // 2. Update the project
        $project->fill($validated);
        if ($project->status === 'initiated' && !empty($project->start_date) && !empty($project->end_date)) {
        $project->status = 'in_progress';
        }

        // 3 Save the project
        $project->save();

        // 4. Redirect back to the same page with a success message
        return redirect()->route('projects.show', $project)
                         ->with('success', 'Project details updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }

    /**
     * Mark the project as completed.
     */
    public function markAsComplete(Project $project)
    {
        if ($project->status === 'in_progress') {
            $project->status = 'completed';
            $project->actual_end_date = now()->toDateString();
            $project->save();
            return redirect()->route('projects.show', $project)->with('success', 'Project marked as Completed.');
        }
        return redirect()->route('projects.show', $project)->with('error', 'Project must be In Progress to be marked as Completed.');
    }

    /**
     * Mark the project as closed.
     */
    public function markAsClosed(Project $project)
    {
        // Add validation/checks if needed (e.g., ensure it's 'completed' first)
        if ($project->status === 'completed') {
            $project->status = 'closed';
            $project->save();
            return redirect()->route('projects.show', $project)->with('success', 'Project marked as Closed.');
        }
        return redirect()->route('projects.show', $project)->with('error', 'Project must be Completed to be marked as Closed.');
    }

    /**
     * Display the project scheduler page.
     */
    public function showScheduler(Project $project)
    {
        // Load the entire WBS, including all nested children
        $project->load('quotation.items.children');
        
        // We get the root items from the quotation
        $items = $project->quotation->items;

        return view('projects.scheduler', compact('project', 'items'));
    }

    /**
     * Store the planned dates from the project scheduler.
     */
    public function storeScheduler(Request $request, Project $project)
    {
        // 1. Validate the incoming data
        // We expect an array of items
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:quotation_items,id',
            'items.*.planned_start' => 'nullable|date',
            'items.*.planned_end' => 'nullable|date|after_or_equal:items.*.planned_start',
        ]);

        // 2. Get all IDs from the project's quotation to be safe
        $allowedItemIds = $project->quotation->allItems()->pluck('id');

        DB::beginTransaction();
        try {
            // 3. Loop and update each item
            foreach ($validated['items'] as $itemData) {
                
                // Security Check: Ensure the item belongs to this project's quotation
                if ($allowedItemIds->contains($itemData['id'])) {
                    
                    // Find the item and update it
                    QuotationItem::where('id', $itemData['id'])->update([
                        'planned_start' => $itemData['planned_start'],
                        'planned_end' => $itemData['planned_end'],
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving schedule: ' . $e->getMessage());
        }

        // 4. Redirect back with success
        return redirect()->route('projects.show', $project)->with('success', 'Project schedule updated successfully.');
    }
}
