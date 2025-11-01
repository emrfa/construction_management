<?php

namespace App\Http\Controllers;

use App\Models\WorkType;
use App\Models\WorkItem;
use App\Models\UnitRateAnalysis;

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
        $allAHS = UnitRateAnalysis::orderBy('code')->get();
        
        return view('work-types.create', [
            'allWorkItems' => $allWorkItems,
            'allAHS' => $allAHS,
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
            'work_items.*' => 'exists:work_items,id',
            'ahs_items' => 'nullable|array',
            'ahs_items.*' => 'exists:unit_rate_analyses,id',
        ]);

        $workType = WorkType::create([
            'name' => $validated['name'],
        ]);

        if ($request->has('work_items')) {
            $workType->workItems()->sync($validated['work_items']);
        }
        
        if ($request->has('ahs_items')) {
            $workType->unitRateAnalyses()->sync($validated['ahs_items']);
        }

        return redirect()->route('work-types.index')
            ->with('success', 'Work Type created successfully.');
    }

    /**
     * Display the specified resource.
     * --- FIX: Changed $workType to $work_type ---
     */
    public function show(WorkType $work_type)
    {
        // Use the consistent $work_type variable
        $work_type->load('workItems', 'unitRateAnalyses');
        return view('work-types.show', compact('work_type'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkType $work_type)
    {
        $allWorkItems = WorkItem::orderBy('name')->get();
        $selectedWorkItems = $work_type->workItems->pluck('id')->toArray();

        $allAHS = UnitRateAnalysis::orderBy('code')->get();
        $selectedAHS = $work_type->unitRateAnalyses->pluck('id')->toArray();

        return view('work-types.edit', compact(
            'work_type',
            'allWorkItems',
            'selectedWorkItems',
            'allAHS',
            'selectedAHS'
        ));
    }

    /**
     * Update the specified resource in storage.
     * --- FIX: Changed $workType to $work_type ---
     */
    public function update(Request $request, WorkType $work_type)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('work_types')->ignore($work_type->id)],
            'work_items' => 'nullable|array',
            'work_items.*' => 'exists:work_items,id',
            'ahs_items' => 'nullable|array',
            'ahs_items.*' => 'exists:unit_rate_analyses,id',
        ]);

        // Use the consistent $work_type variable
        $work_type->update([
            'name' => $validated['name'],
        ]);

        $work_type->workItems()->sync($request->input('work_items', []));
        $work_type->unitRateAnalyses()->sync($request->input('ahs_items', []));

        return redirect()->route('work-types.show', $work_type)
            ->with('success', 'Work Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkType $work_type)
    {
        try {
            $work_type->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint violation
            if ($e->getCode() == 23000) {
                return redirect()->route('work-types.index')
                    ->with('error', 'Cannot delete Work Type. It is already being used.');
            }
            throw $e;
        }

            return redirect()->route('work-types.index')
                ->with('success', 'Work Type deleted successfully.');
    }
}