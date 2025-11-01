<?php

namespace App\Http\Controllers;

use App\Models\WorkItem;
use App\Models\WorkType;
use App\Models\UnitRateAnalysis;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $workItems = WorkItem::orderBy('name')->paginate(15);
    
        return view('work-items.index', compact('workItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       $allAHS = UnitRateAnalysis::orderBy('code')->get();

        return view('work-items.create', [
            'allAHS' => $allAHS
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $validated = $request->validate([
            'name' => 'required|string|max:255|unique:work_items',
            'ahs_items' => 'nullable|array',
            'ahs_items.*' => 'exists:unit_rate_analyses,id'
        ]);

        $workItem = WorkItem::create([
            'name' => $validated['name'],
        ]);

        // Attach the selected AHS components
        if ($request->has('ahs_items')) {
            $workItem->unitRateAnalyses()->sync($validated['ahs_items']);
        }

        return redirect()->route('work-items.index')
                     ->with('success', 'Work Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkItem $workItem)
    {
        $workItem->load('unitRateAnalyses');

        return view('work-items.show', compact('workItem'));
    }   

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkItem $workItem)
    {
       $allAHS = UnitRateAnalysis::orderBy('code')->get();

       
        $selectedAHS = $workItem->unitRateAnalyses->pluck('id')->toArray();

        return view('work-items.edit', [
            'workItem' => $workItem,
            'allAHS' => $allAHS,
            'selectedAHS' => $selectedAHS,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkItem $workItem)
    {
       // Validate the data
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('work_items')->ignore($workItem->id),
            ],
            'ahs_items' => 'nullable|array',
            'ahs_items.*' => 'exists:unit_rate_analyses,id'
        ]);

        // Update the Work Item's name
        $workItem->update([
            'name' => $validated['name'],
        ]);
        
        $workItem->unitRateAnalyses()->sync($request->input('ahs_items', []));

        return redirect()->route('work-items.index')
                        ->with('success', 'Work Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkItem $workItem)
    {
        // No need for try-catch, as deleting this won't break other tables
        $workItem->delete();
        return redirect()->route('work-items.index')
                         ->with('success', 'Work Item deleted successfully.');
    }
}