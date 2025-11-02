<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ItemCategory::orderBy('name')->paginate(15);
        return view('item-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('item-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:item_categories',

        ]);
        ItemCategory::create($validated);

        return redirect()->route('item-categories.index')
                         ->with('success', 'Item category created successfully.');
    }

    /**
     * Display the specified resource.
     * (We'll skip this for now as index/edit is enough)
     */
    public function show(ItemCategory $itemCategory)
    {
        return redirect()->route('item-categories.edit', $itemCategory);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemCategory $itemCategory)
    {
        return view('item-categories.edit', compact('itemCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemCategory $itemCategory)
    {
       $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('item_categories')->ignore($itemCategory->id),
            ],
        ]);

        $itemCategory->update($validated);
        

        return redirect()->route('item-categories.index')
                         ->with('success', 'Item category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemCategory $itemCategory)
    {
        try {
            $itemCategory->delete();
            return redirect()->route('item-categories.index')
                             ->with('success', 'Item category deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Check for foreign key constraint violation
            if ($e->getCode() == 23000) {
                return redirect()->route('item-categories.index')
                                 ->with('error', 'Cannot delete this category because it is being used by inventory items.');
            }
            return redirect()->route('item-categories.index')
                             ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}