<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Exports\InventoryItemsExport;
use App\Imports\InventoryItemsImport;
use Maatwebsite\Excel\Facades\Excel;


use Illuminate\Support\Str;
use Maatwebsite\Excel\HeadingRowImport;

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

    public function export(Request $request)
    {
        // Validate that selected_ids, if present, is an array
        $request->validate([
            'selected_ids' => 'nullable|array'
        ]);

        $ids = $request->input('selected_ids', null);

        return Excel::download(new InventoryItemsExport($ids), 'inventory_items.xlsx');
    }

    public function showImportForm()
    {
        return view('inventory-items.import');
    }

    /**
     * STEP 1: Analyze the uploaded file for problems.
     */
    public function analyzeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        // 1. Store the file temporarily
        $path = $request->file('file')->store('temp_imports');
        $request->session()->put('import_file_path', $path);

        // 2. Read all rows from the file as a plain array
        try {
            $allRows = Excel::toArray(new \stdClass(), $path)[0]; // [0] gets the first sheet
        } catch (\Exception $e) {
            return back()->with('error', 'Could not read the file. Error: ' . $e->getMessage());
        }

        // 3. Get the heading row (and remove it from $allRows)
        $headings = array_map('trim', array_shift($allRows));

        // 4. Find missing categories
        $existingCategories = ItemCategory::pluck('name')->map('strtolower');

        $problemRows = collect();
        
        // Loop through the *data rows*
        foreach ($allRows as $index => $rowArray) {
            $row = [];
            foreach ($headings as $i => $heading) {
                $row[$heading] = $rowArray[$i] ?? null;
            }
            
            $categoryName = $row['category_name'] ?? null;

            if (empty($categoryName)) {
                continue; // Skip rows without a category
            }
            
            $nameLower = strtolower($categoryName);

            // Check if this name is a problem
            if (!$existingCategories->contains($nameLower)) {
                $problemRows->push(['name' => $categoryName]);
            }
        }

        $uniqueProblems = $problemRows->unique('name');

        // If no problems, just process it immediately
        if ($uniqueProblems->isEmpty()) {
            return $this->processImport($request);
        }

        // We have problems, so let's find suggestions
        $problemsWithSuggestions = [];
        foreach ($uniqueProblems as $problem) {
            $suggestions = [];

            // Find "Did you mean?"
            foreach ($existingCategories as $existingName) {
                similar_text(strtolower($problem['name']), $existingName, $percent);
                if ($percent >= 75) { // 75% match or higher
                    // Find the original case-sensitive name to suggest
                    $originalName = ItemCategory::where(DB::raw('LOWER(name)'), $existingName)->value('name');
                    $suggestions[] = $originalName;
                }
            }
            
            $problem['suggestions'] = array_unique($suggestions);
            $problemsWithSuggestions[] = $problem;
        }

        // Store problems in session and redirect to confirmation
        $request->session()->put('import_problems', $problemsWithSuggestions);
        return redirect()->route('inventory-items.import.confirm');
    }

    /**
     * STEP 2: Show the confirmation page with problems.
     */
    public function showConfirmForm(Request $request)
    {
        $problems = $request->session()->get('import_problems');
        $filePath = $request->session()->get('import_file_path');

        if (!$problems || !$filePath) {
            return redirect()->route('inventory-items.importForm')->with('error', 'No import data found. Please upload a file again.');
        }

        return view('inventory-items.import-confirm', [
            'problems' => $problems
        ]);
    }

    /**
     * STEP 3: Process the import with user's resolutions.
     * (This REPLACES your old processImport method)
     */
    public function processImport(Request $request)
    {
        $filePath = $request->session()->get('import_file_path');
        if (!$filePath) {
            return redirect()->route('inventory-items.importForm')->with('error', 'Your session expired. Please upload the file again.');
        }

        $resolutions = $request->input('resolutions', []);
        
        $fixMap = []; // This will map "Typo Name" -> "Correct Name"
        
        // --- THIS IS THE NEW LOGIC ---
        // First, create any new categories the user approved
        foreach ($resolutions as $problemName => $action) {
            if ($action === 'create_new') {
                // Create the new category. The model's boot() method will make the prefix.
                $newCategory = ItemCategory::create(['name' => $problemName]);
                // Add it to our "fix map" so the importer knows what to do
                $fixMap[strtolower($problemName)] = strtolower($newCategory->name);
            }
            // Add "Did you mean?" fixes to the map
            elseif (Str::startsWith($action, 'Use: ')) {
                $fixMap[strtolower($problemName)] = strtolower(Str::after($action, 'Use: '));
            }
            // Add "skip" to the map
            elseif ($action === 'skip') {
                $fixMap[strtolower($problemName)] = 'skip';
            }
        }
        // --- END NEW LOGIC ---

        try {
            // Pass the fix map to the import class
            Excel::import(new InventoryItemsImport($fixMap), $filePath);
            
            // Clean up session
            $request->session()->forget(['import_file_path', 'import_problems']);
            
            return redirect()->route('inventory-items.index')
                             ->with('success', 'Items imported successfully.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $errorMessages = [];
             foreach ($failures as $failure) {
                 $attribute = $failure->attribute();
                 $value = $failure->values()[$attribute] ?? '[Not Available]';
                 $errorMessages[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors()) . ' (Attribute: ' . $attribute . ', Value: ' . $value . ')';
             }
             return back()->with('error', 'Error during import: <br>' . implode('<br>', $errorMessages));
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }
}
