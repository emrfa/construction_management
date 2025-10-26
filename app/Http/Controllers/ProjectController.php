<?php

namespace App\Http\Controllers;

use App\Models\Project;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // We use with('client') to efficiently get the client's name
        $projects = Project::with('client')->latest()->get();

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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => [
                'required',
                Rule::in(['initiated', 'in_progress', 'completed', 'closed']),
            ],
        ]);

        // 2. Update the project
        $project->update($validated);

        // 3. Redirect back to the same page with a success message
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
}
