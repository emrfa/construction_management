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
        $query = Project::with(['client', 'quotation', 'quotation.allItems.progressUpdates'])
            ->latest();

        // Apply search logic
        $query->when($request->search, function ($q, $search) {
            return $q->where('project_code', 'like', "%{$search}%")
                     ->orWhereHas('quotation', function ($subQuery) use ($search) {
                         $subQuery->where('project_name', 'like', "%{$search}%");
                     })
                     ->orWhereHas('client', function ($subQuery) use ($search) {
                         $subQuery->where('name', 'like', "%{$search}%");
                     });
        });

        // [NEW] Handle Risk Filters (requires calculation)
        if ($request->has('filter')) {
            // We must fetch all to calculate metrics, then filter
            // Note: This might be heavy if there are thousands of projects, 
            // but for a typical construction firm with < 100 active projects, it's fine.
            $projectsCollection = $query->get();
            
            // Helper to calculate metrics (duplicated from Dashboard - ideally move to Service/Trait)
            $projectsCollection->each(function($project) {
                // We need these for filtering
                $project->actual_progress = $this->getWbsActualProgress($project);
                $project->planned_progress = $this->getWbsPlannedProgress($project);
                
                $project->actual_cost = $project->quotation->allItems->sum('actual_cost');
                $project->earned_value = (float)$project->total_budget * ($project->actual_progress / 100);
                $project->cost_variance = $project->earned_value - $project->actual_cost;
            });

            if ($request->filter === 'over_budget') {
                $projectsCollection = $projectsCollection->filter(function ($project) {
                    return $project->cost_variance < 0 && ($project->status == 'initiated' || $project->status == 'in_progress');
                });
            } elseif ($request->filter === 'delayed') {
                $projectsCollection = $projectsCollection->filter(function ($project) {
                    return $project->actual_progress < $project->planned_progress && ($project->status == 'initiated' || $project->status == 'in_progress');
                });
            }

            // Manual Pagination
            $page = $request->get('page', 1);
            $perPage = 15;
            $projects = new \Illuminate\Pagination\LengthAwarePaginator(
                $projectsCollection->forPage($page, $perPage),
                $projectsCollection->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

        } else {
            // Standard Pagination
            $projects = $query->paginate(15)->appends($request->query());
        }

        return view('projects.index', compact('projects'));
    }

    // [DUPLICATED HELPERS] - In a real refactor, move these to a ProjectService or Trait
    private function getWbsActualProgress(Project $project): float
    {
        $totalBudget = (float) $project->total_budget;
        if ($totalBudget == 0) return 0;

        $tasks = $project->quotation->allItems->filter(fn($item) => $item->children->isEmpty());
        $totalEarnedValue = 0;

        foreach ($tasks as $task) {
            $taskWeight = (float)$task->subtotal;
            $taskProgress = (float)$task->latest_progress;
            $totalEarnedValue += $taskWeight * ($taskProgress / 100);
        }

        return round(($totalEarnedValue / $totalBudget) * 100, 2);
    }

    private function getWbsPlannedProgress(Project $project): float
    {
        $totalBudget = (float) $project->total_budget;
        if ($totalBudget == 0) return 0;

        $project->loadMissing('quotation.allItems');
        $tasks = $project->quotation->allItems->filter(fn($item) => $item->children->isEmpty());
        $today = \Carbon\Carbon::today();
        $totalPlannedValue = 0;

        foreach ($tasks as $task) {
            $taskWeight = (float)$task->subtotal;
            $planned_start = $task->planned_start ? \Carbon\Carbon::parse($task->planned_start) : null;
            $planned_end = $task->planned_end ? \Carbon\Carbon::parse($task->planned_end) : null;

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
