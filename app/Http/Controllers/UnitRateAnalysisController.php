<?php

namespace App\Http\Controllers;

use App\Models\UnitRateAnalysis;
use App\Models\InventoryItem;
use App\Models\LaborRate;
use App\Models\UnitRateMaterial;
use App\Models\UnitRateLabor;

use App\Exports\UnitRateAnalysisExport;
use App\Imports\UnitRateAnalysisImport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Str;
use Maatwebsite\Excel\HeadingRowImport;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UnitRateAnalysisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $analyses = UnitRateAnalysis::orderBy('code')->paginate(20);
        return view('ahs-library.index', compact('analyses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $inventoryItems = InventoryItem::orderBy('item_name')->get();
        $laborRates = LaborRate::orderBy('labor_type')->get();
        return view('ahs-library.create', compact('inventoryItems', 'laborRates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|string|max:50|unique:unit_rate_analyses',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'overhead_profit_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'materials' => 'nullable|array',
            'materials.*.inventory_item_id' => 'required_with:materials|exists:inventory_items,id',
            'materials.*.coefficient' => 'required_with:materials|numeric|min:0',
            'materials.*.unit_cost' => 'required_with:materials|numeric|min:0',
            'labors' => 'nullable|array',
            'labors.*.labor_rate_id' => 'required_with:labors|exists:labor_rates,id',
            'labors.*.coefficient' => 'required_with:labors|numeric|min:0',
            'labors.*.rate' => 'required_with:labors|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create the AHS Header (total_cost will be calculated later)
            $analysis = UnitRateAnalysis::create([
                'code' => $validatedData['code'],
                'name' => $validatedData['name'],
                'unit' => $validatedData['unit'],
                'overhead_profit_percentage' => $validatedData['overhead_profit_percentage'],
                'notes' => $validatedData['notes'],
                'total_cost' => 0, // Initial value
            ]);

            // Add Materials
            if (!empty($validatedData['materials'])) {
                foreach ($validatedData['materials'] as $materialData) {
                     // Skip if item ID is missing (e.g., empty row)
                    if (empty($materialData['inventory_item_id'])) continue;
                    $analysis->materials()->create([
                        'inventory_item_id' => $materialData['inventory_item_id'],
                        'coefficient' => $materialData['coefficient'],
                        'unit_cost' => $materialData['unit_cost'],
                    ]);
                }
            }

            // Add Labors
            if (!empty($validatedData['labors'])) {
                foreach ($validatedData['labors'] as $laborData) {
                    // Skip if item ID is missing
                    if (empty($laborData['labor_rate_id'])) continue;
                    $analysis->labors()->create([
                        'labor_rate_id' => $laborData['labor_rate_id'],
                        'coefficient' => $laborData['coefficient'],
                        'rate' => $laborData['rate'],
                    ]);
                }
            }

            // Recalculate and save the total cost
            $analysis->recalculateTotalCost();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error creating AHS: ' . $e->getMessage());
        }

        return redirect()->route('ahs-library.index')->with('success', 'AHS created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnitRateAnalysis $ahs_library)
    {
        $ahs_library->load(['materials.inventoryItem', 'labors.laborRate']);
        return view('ahs-library.show', compact('ahs_library'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitRateAnalysis $ahs_library)
    {
        // Load relationships needed for the form
        $ahs_library->load(['materials.inventoryItem', 'labors.laborRate']);

        // Fetch master data for dropdowns
        $inventoryItems = InventoryItem::orderBy('item_name')->get();
        $laborRates = LaborRate::orderBy('labor_type')->get();

        return view('ahs-library.edit', compact('ahs_library', 'inventoryItems', 'laborRates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnitRateAnalysis $ahs_library)
    {
        $validatedData = $request->validate([
            // Unique check ignores the current AHS item's code
            'code' => ['required','string','max:50', Rule::unique('unit_rate_analyses')->ignore($ahs_library->id)],
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'overhead_profit_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'materials' => 'nullable|array',
            'materials.*.inventory_item_id' => 'required_with:materials|exists:inventory_items,id',
            'materials.*.coefficient' => 'required_with:materials|numeric|min:0',
            'materials.*.unit_cost' => 'required_with:materials|numeric|min:0',
            'labors' => 'nullable|array',
            'labors.*.labor_rate_id' => 'required_with:labors|exists:labor_rates,id',
            'labors.*.coefficient' => 'required_with:labors|numeric|min:0',
            'labors.*.rate' => 'required_with:labors|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update the AHS Header
            $ahs_library->update([
                'code' => $validatedData['code'],
                'name' => $validatedData['name'],
                'unit' => $validatedData['unit'],
                'overhead_profit_percentage' => $validatedData['overhead_profit_percentage'],
                'notes' => $validatedData['notes'],
            ]);

            // Sync Materials (Delete old, add new)
            $ahs_library->materials()->delete(); // Remove existing
            if (!empty($validatedData['materials'])) {
                foreach ($validatedData['materials'] as $materialData) {
                    if (empty($materialData['inventory_item_id'])) continue;
                    $ahs_library->materials()->create([ // Add new
                        'inventory_item_id' => $materialData['inventory_item_id'],
                        'coefficient' => $materialData['coefficient'],
                        'unit_cost' => $materialData['unit_cost'],
                    ]);
                }
            }

            // Sync Labors (Delete old, add new)
            $ahs_library->labors()->delete(); // Remove existing
            if (!empty($validatedData['labors'])) {
                foreach ($validatedData['labors'] as $laborData) {
                    if (empty($laborData['labor_rate_id'])) continue;
                    $ahs_library->labors()->create([ // Add new
                        'labor_rate_id' => $laborData['labor_rate_id'],
                        'coefficient' => $laborData['coefficient'],
                        'rate' => $laborData['rate'],
                    ]);
                }
            }

            // Recalculate and save the total cost
            $ahs_library->recalculateTotalCost();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error updating AHS: ' . $e->getMessage());
        }

        // Redirect to show page after update
        return redirect()->route('ahs-library.show', $ahs_library)->with('success', 'AHS updated successfully.');
    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitRateAnalysis $unitRateAnalysis)
    {
        try {
            DB::beginTransaction();
            // Deleting the header will cascade delete materials/labors due to DB constraints
            $ahs_library->delete();
            DB::commit();
        } catch (\Exception $e) {
             DB::rollBack();
             return redirect()->route('ahs-library.index')->with('error', 'Error deleting AHS: ' . $e->getMessage());
        }

        return redirect()->route('ahs-library.index')
                         ->with('success', 'AHS deleted successfully.');
    }

    public function export(Request $request)
    {
        // Validate that selected_ids, if present, is an array
        $request->validate([
            'selected_ids' => 'nullable|array'
        ]);

        $ids = $request->input('selected_ids', null);

        return Excel::download(new UnitRateAnalysisExport($ids), 'ahs_library.xlsx');
    }

    /**
     * Show the form for importing AHS items.
     */
    public function showImportForm()
    {
        return view('ahs-library.import');
    }

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
            return back()->with('error', 'Could not read the file. Is it password protected? Error: ' . $e->getMessage());
        }

        // 3. Get the heading row (and remove it from $allRows)
        $headings = array_shift($allRows);

        // 4. Find missing items
        $existingMaterials = InventoryItem::pluck('item_name')->map('strtolower');
        $existingLabors = LaborRate::pluck('labor_type')->map('strtolower');

        $problemRows = collect();
        
        // Loop through the *data rows*
        foreach ($allRows as $index => $rowArray) {
            
            // Combine the headings with the current row's data
            $row = [];
            foreach ($headings as $i => $heading) {
                // Trim whitespace from heading to be safe
                $row[trim($heading)] = $rowArray[$i] ?? null;
            }
            
            // --- FIX 1: Use the correct, exact column header ---
            $type = $row['component_type'] ?? null;
            $name = $row['component_name (Used for Match)'] ?? null; // Was 'component_name_used_for_match'

            if (empty($type) || empty($name)) {
                continue; // Skip header rows or blank rows
            }
            
            $nameLower = strtolower($name);

            // Check if this name is a problem
            if ($type == 'Material' && !$existingMaterials->contains($nameLower)) {
                $problemRows->push(['type' => 'Material', 'name' => $name]);
            } elseif ($type == 'Labor' && !$existingLabors->contains($nameLower)) {
                $problemRows->push(['type' => 'Labor', 'name' => $name]);
            }
        }

        $uniqueProblems = $problemRows->unique('name');

        // If no problems, just process it immediately
        if ($uniqueProblems->isEmpty()) {
            // We'll pass the request, but the import will pull the file from session
            return $this->processImport($request);
        }

        // We have problems, so let's find suggestions
        $problemsWithSuggestions = [];
        foreach ($uniqueProblems as $problem) {
            $suggestions = [];
            $source = ($problem['type'] == 'Material') ? $existingMaterials : $existingLabors;

            // Find "Did you mean?"
            foreach ($source as $existingName) {
                // Use built-in similar_text to find close matches
                similar_text(strtolower($problem['name']), $existingName, $percent);
                if ($percent >= 75) { // 75% match or higher
                    // Find the original case-sensitive name to suggest
                    $originalName = InventoryItem::where(DB::raw('LOWER(item_name)'), $existingName)->value('item_name') 
                                 ?? LaborRate::where(DB::raw('LOWER(labor_type)'), $existingName)->value('labor_type');
                    $suggestions[] = $originalName;
                }
            }
            
            $problem['suggestions'] = array_unique($suggestions); // Ensure suggestions are unique
            
            // --- FIX 2: Add $ to variable name ---
            $problemsWithSuggestions[] = $problem; // Was 'problemsWithSuggestions[]'
        }

        // Store problems in session and redirect to confirmation
        $request->session()->put('import_problems', $problemsWithSuggestions);
        return redirect()->route('ahs-library.import.confirm');
    }

    /**
     * STEP 2: Show the confirmation page with problems.
     */
    public function showConfirmForm(Request $request)
    {
        $problems = $request->session()->get('import_problems');
        $filePath = $request->session()->get('import_file_path');

        if (!$problems || !$filePath) {
            return redirect()->route('ahs-library.importForm')->with('error', 'No import data found. Please upload a file again.');
        }

        return view('ahs-library.import-confirm', [
            'problems' => $problems
        ]);
    }

    /**
     * Handle the import of AHS items.
     */
    public function processImport(Request $request)
    {
       $filePath = $request->session()->get('import_file_path');
        if (!$filePath) {
            return redirect()->route('ahs-library.importForm')->with('error', 'Your session expired. Please upload the file again.');
        }

        $resolutions = $request->input('resolutions', []);
        
        // This is where you would handle creating new items.
        // For now, we will just build a "map" of fixes.
        $fixMap = [];
        foreach ($resolutions as $problemName => $action) {
            // $action could be "skip", "create_new", or an existing name "Use: Concrete"
            if (Str::startsWith($action, 'Use: ')) {
                $fixMap[strtolower($problemName)] = strtolower(Str::after($action, 'Use: '));
            } elseif ($action === 'skip') {
                $fixMap[strtolower($problemName)] = 'skip';
            }
            // We'll skip 'create_new' for now, as it requires creating new ItemCategories/LaborRates
        }

        try {
            // Pass the fix map to the import class
            Excel::import(new UnitRateAnalysisImport($fixMap), $filePath);
            
            // Clean up session
            $request->session()->forget(['import_file_path', 'import_problems']);
            
            return redirect()->route('ahs-library.index')
                             ->with('success', 'AHS Library imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }
}
