<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = InventoryItem::with('stockTransactions')->orderBy('item_name')->get();
        return view('inventory-items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventory-items.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:inventory_items',
            'item_name' => 'required|string|max:255|unique:inventory_items',
            'category' => 'nullable|string|max:255',
            'uom' => 'required|string|max:50',
            'reorder_level' => 'required|numeric|min:0',
        ]);

        // 2. Create
        InventoryItem::create($validated);

        // 3. Redirect
        return redirect()->route('inventory-items.index')
                         ->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryItem $inventoryItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryItem $inventoryItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryItem $inventoryItem)
    {
        //
    }
}
