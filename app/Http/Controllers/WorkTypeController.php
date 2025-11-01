<?php

namespace App\Http\Controllers;

use App\Models\WorkType;
use App\Models\WorkItem;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workTypes = WorkType::orderBy('name')->paginate(15);
        return view('work-types.index', compact('workTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allWorkItems = WorkItem::orderBy('name')->get();
        
        return view('work-types.create', [
            'allWorkItems' => $allWorkItems
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:work_types',
            'work_items' => 'nullable|array',
            'work_items.*' => 'exists:work_items,id'
        ]);

        $workType = WorkType::create([
            'name' => $validated['name'],
        ]);

        if ($request->has('work_items')) {
            $workType->workItems()->sync($validated['work_items']);
        }

        return redirect()->route('work-types.index')
                         ->with('success', 'Work Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkType $workType)
    {
        $workType->load('workItems');
        
        return view('work-types.show', compact('workType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkType $workType)
    {
        $allWorkItems = WorkItem::orderBy('name')->get();

        $selectedWorkItems = $workType->workItems->pluck('id')->toArray();

        return view('work-types.edit', [
            'workType' => $workType,
            'allWorkItems' => $allWorkItems,
            'selectedWorkItems' => $selectedWorkItems,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkType $workType)
    {
        $validated = $request->validate([
                'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('work_types')->ignore($workType->id),
            ],
            'work_items' => 'nullable|array',
            'work_items.*' => 'exists:work_items,id'
        ]);

        $workType->update([
            'name' => $validated['name'],
        ]);

        // Re-sync the "recipe" of Work Items
        $workType->workItems()->sync($request->input('work_items', []));

        return redirect()->route('work-types.index')
                         ->with('success', 'Work Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkType $workType)
    {
        try {
            $workType->delete();
            return redirect()->route('work-types.index')
                             ->with('success', 'Work Type deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint violation
            return redirect()->route('work-types.index')
                             ->with('error', 'Cannot delete Work Type. It is already being used by Work Items.');
        }
    }
}
