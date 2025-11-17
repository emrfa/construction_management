<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Equipment;
use App\Models\Supplier;


class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start query
        $query = Equipment::with('supplier')->latest();
        
        // **NEW**: Apply search logic
        $query->when($request->search, function ($q, $search) {
            return $q->where('name', 'like', "%{$search}%")
                     ->orWhere('identifier', 'like', "%{$search}%")
                     ->orWhere('type', 'like', "%{$search}%");
        });

        // [MODIFIED] Paginate the query
        $equipmentItems = $query->paginate(15)->appends($request->query());

        return view('equipment.index', compact('equipmentItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('equipment.create', compact('suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'nullable|string|max:255|unique:equipment',
            'type' => 'nullable|string|max:255',
            'status' => [
                'required', 
                Rule::in(['owned', 'rented', 'maintenance', 'disposed', 'pending_acquisition'])
            ],
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date|after_or_equal:rental_start_date',
            'rental_rate' => 'nullable|numeric|min:0',
            'rental_rate_unit' => 'nullable|string|max:50',
            'base_purchase_price' => 'nullable|numeric|min:0',
            'base_rental_rate' => 'nullable|numeric|min:0',
            'base_rental_rate_unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        // 2. Create the new equipment
        Equipment::create($validated);

        // 3. Redirect back to the index page
        return redirect()->route('equipment.index')
                         ->with('success', 'Equipment created successfully.');
    }

    /**
     * Display the specified resource.
     * We'll just redirect to the edit page as a 'show' page is often redundant.
     * FIX: Use Route-Model Binding (Equipment $equipment)
     */
    public function show(Equipment $equipment)
    {
        // Redirect to the edit view
        return redirect()->route('equipment.edit', $equipment);
    }

    /**
     * Show the form for editing the specified resource.
     * FIX: Use Route-Model Binding (Equipment $equipment)
     */
    public function edit(Equipment $equipment)
    {
        $suppliers = Supplier::orderBy('name')->get();
        // The $equipment model is automatically fetched by Laravel
        return view('equipment.edit', compact('equipment', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     * FIX: Use Route-Model Binding (Equipment $equipment)
     */
    public function update(Request $request, Equipment $equipment)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => [
                'nullable', 'string', 'max:255',
                Rule::unique('equipment')->ignore($equipment->id) // Ignore its own ID
            ],
            'type' => 'nullable|string|max:255',
            'status' => [
                'required', 
                Rule::in(['owned', 'rented', 'maintenance', 'disposed', 'pending_acquisition'])
            ],
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date|after_or_equal:rental_start_date',
            'rental_rate' => 'nullable|numeric|min:0',
            'rental_rate_unit' => 'nullable|string|max:50',
            'base_purchase_price' => 'nullable|numeric|min:0',
            'base_rental_rate' => 'nullable|numeric|min:0',
            'base_rental_rate_unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        // 2. Update the equipment
        $equipment->update($validated);

        // 3. Redirect back to the index page
        return redirect()->route('equipment.index')
                         ->with('success', 'Equipment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * FIX: Use Route-Model Binding (Equipment $equipment)
     */
    public function destroy(Equipment $equipment)
    {
        try {
            // 1. Delete the equipment
            $equipment->delete();
            
            // 2. Redirect with success
            return redirect()->route('equipment.index')
                             ->with('success', 'Equipment deleted successfully.');

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint violation (if it's used in AHS, etc.)
            if ($e->getCode() == 23000) {
                return redirect()->route('equipment.index')
                                 ->with('error', 'Cannot delete this equipment, it is in use.');
            }
            // Handle other potential errors
            return redirect()->route('equipment.index')
                             ->with('error', 'An error occurred while deleting the equipment.');
        }
    }
}