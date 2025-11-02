<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Exports\InventoryItemsExport;
use App\Imports\InventoryItemsImport;
use Maatwebsite\Excel\Facades\Excel;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get categories for the filter dropdown
        $categories = ItemCategory::orderBy('name')->get();

        // Start the query
        $query = InventoryItem::query()
            ->with('itemCategory') 
            ->orderBy('item_name');

        // Apply search filter (if present)
        $query->when($request->search, function ($q, $search) {
            return $q->where('item_name', 'like', "%{$search}%")
                     ->orWhere('item_code', 'like', "%{$search}%");
        });

        // Apply category filter (if present)
        $query->when($request->category, function ($q, $category_id) {
            return $q->where('category_id', $category_id);
        });

        // Paginate the results
        $items = $query->paginate(15)->appends($request->query());

        // Pass all data to the view
        return view('inventory-items.index', [
            'items' => $items,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\ItemCategory::orderBy('name')->get();
        
        return view('inventory-items.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|exists:item_categories,id',
            'uom' => 'required|string|max:50',
            'base_purchase_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        // 2. Create
        InventoryItem::create($validated);

        // 3. Redirect
        return redirect()->route('inventory-items.index')
                         ->with('success', 'Item created successfully. Code was auto-generated.');
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
        $categories = ItemCategory::orderBy('name')->get();
        return view('inventory-items.edit', compact('inventoryItem', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        // 1. Validate
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|exists:item_categories,id',
            'uom' => 'required|string|max:50',
            'base_purchase_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            // We don't validate item_code as it's not editable
        ]);

        // 2. Update
        $inventoryItem->update($validated);

        // 3. Redirect
        return redirect()->route('inventory-items.index')
                         ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryItem $inventoryItem)
    {
        try {
            $inventoryItem->delete();
            return redirect()->route('inventory-items.index')
                             ->with('success', 'Item deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('inventory-items.index')
                                 ->with('error', 'Cannot delete this item, it is in use.');
            }
            return redirect()->route('inventory-items.index')
                             ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new InventoryItemsExport, 'inventory_items.xlsx');
    }

    public function showImportForm()
    {
        return view('inventory-items.import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new InventoryItemsImport, $request->file('file'));
            
            return redirect()->route('inventory-items.index')
                             ->with('success', 'Items imported successfully.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $errorMessages = [];
             foreach ($failures as $failure) {
                 $errorMessages[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors()) . ' (Value: ' . $failure->values()[$failure->attribute()] . ')';
             }
             return back()->with('error', 'Error during import: <br>' . implode('<br>', $errorMessages));
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }
}
