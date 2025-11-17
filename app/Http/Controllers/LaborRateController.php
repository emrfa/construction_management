<?php

namespace App\Http\Controllers;

use App\Models\LaborRate;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class LaborRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start query
        $query = LaborRate::query()->orderBy('labor_type');

        // **NEW**: Apply search logic
        $query->when($request->search, function ($q, $search) {
            return $q->where('labor_type', 'like', "%{$search}%");
        });
        
        // [MODIFIED] Paginate the query
        $laborRates = $query->paginate(15)->appends($request->query());
        
        return view('labor-rates.index', compact('laborRates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('labor-rates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'labor_type' => 'required|string|max:255|unique:labor_rates',
            'unit' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0',
        ]);

        LaborRate::create($validated);

        return redirect()->route('labor-rates.index')
                         ->with('success', 'Labor rate created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LaborRate $laborRate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LaborRate $laborRate)
    {
        return view('labor-rates.edit', compact('laborRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LaborRate $laborRate)
    {
        $validated = $request->validate([
            // Ensure unique check ignores the current record
            'labor_type' => ['required','string','max:255', Rule::unique('labor_rates')->ignore($laborRate->id)],
            'unit' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0',
        ]);

        $laborRate->update($validated);

        return redirect()->route('labor-rates.index')
                         ->with('success', 'Labor rate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaborRate $laborRate)
    {
        $laborRate->delete();

        return redirect()->route('labor-rates.index')
                         ->with('success', 'Labor rate deleted successfully.');
    }
}
