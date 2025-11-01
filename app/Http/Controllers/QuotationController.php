<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Client;
use App\Models\QuotationItem;
use App\Models\Project;
use App\Models\UnitRateAnalysis;
use App\Models\WorkItem;
use App\Models\WorkType;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuotationController extends Controller
{

    public function quotations()
{
    return $this->hasMany(Quotation::class);
}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotations = Quotation::with('client')->latest()->get(); 

        return view('quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       // 1. For the Client dropdown
        $clients = Client::orderBy('name')->get();
        
        // 2. For the manual AHS select dropdowns
        $ahsLibrary = UnitRateAnalysis::orderBy('name')->get();

        // 3. For the new "Pull Work Type" dropdown (with full recipe)
        $workTypesLibrary_json = WorkType::with([
            'workItems.unitRateAnalyses' // Eager load the full recipe
        ])->orderBy('name')->get();
        
        // 4. For the Alpine 'linkAHS' function
        $ahsJsonData = $ahsLibrary->mapWithKeys(fn($ahs) => [$ahs->id => [
            'code' => $ahs->code,
            'name' => $ahs->name,
            'unit' => $ahs->unit,
            'cost' => $ahs->total_cost
        ]]);
        
        // 5. For repopulating the form on a validation error
        $oldItemsArray = old('items_json') ? json_decode(old('items_json'), true) : [];

        // 6. Pass all data to the view
        return view('quotations.create', [
            'clients' => $clients,
            'ahsLibrary' => $ahsLibrary,
            'ahsJsonData' => $ahsJsonData,
            'workTypesLibrary_json' => $workTypesLibrary_json,
            'oldItemsArray' => $oldItemsArray,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the main quotation fields
    $validatedData = $request->validate([
        'client_id' => 'required|exists:clients,id',
        'project_name' => 'required|string|max:255',
        'date' => 'required|date',
        'items_json' => 'required|json', // Validate that it's a valid JSON string
    ]);

    // vvv ADD THIS LINE vvv
    // Decode the JSON string into the array our saveItems method expects
    $itemsArray = json_decode($validatedData['items_json'], true);

    $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.item_code' => 'nullable|string|max:50',
            'items.*.uom' => 'nullable|string|max:50',
             // Allow quantity/price to be null only if it's a parent item
            'items.*.quantity' => ['nullable', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($itemsArray) {
                // Extract index from attribute like 'items.0.quantity'
                $index = explode('.', $attribute)[1];
                // Check if the corresponding item is a parent (has children)
                if (!isset($itemsArray[$index]['children']) || count($itemsArray[$index]['children']) === 0) {
                     // If not a parent, quantity is required and must be > 0 if price is > 0 (or adjust logic as needed)
                     // For simplicity now, just require it for non-parents
                     if ($value === null || $value === '') $fail($attribute.' is required for line items.');
                }
            }],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($itemsArray) {
                $index = explode('.', $attribute)[1];
                if (!isset($itemsArray[$index]['children']) || count($itemsArray[$index]['children']) === 0) {
                    if ($value === null || $value === '') $fail($attribute.' is required for line items.');
                }
            }],
            // Add validation for the AHS ID
            'items.*.unit_rate_analysis_id' => 'nullable|exists:unit_rate_analyses,id', // Must exist if provided
            'items.*.children' => 'nullable|array', // Validate nested children structure if needed
        ]);

        if ($itemsValidator->fails()) {
            return back()->withInput()->withErrors($itemsValidator);
        }

    $grandTotal = 0;

    try {
        // 2. Start a database transaction
        DB::beginTransaction();

        // 3. Create the main Quotation
        // We set total_estimate to 0 for now. We'll update it after saving items.
        $quotation = Quotation::create([
            'client_id' => $validatedData['client_id'],
            'project_name' => $validatedData['project_name'],
            'date' => $validatedData['date'],
            'status' => 'draft', // Default status
            'total_estimate' => 0,
        ]);

        // 4. Call our new recursive function to save items
        $grandTotal = $this->saveItems($itemsArray, $quotation->id, null);

        // 5. Now update the quotation's total_estimate
        $quotation->total_estimate = $grandTotal;
        $quotation->save();

        // 6. Commit the transaction
        DB::commit();

    } catch (\Exception $e) {
        // 7. If anything went wrong, roll back
        DB::rollBack();
        // Optional: return with a specific error message
        return back()->withInput()->withErrors('Error saving quotation: ' . $e->getMessage());
    }

    // 8. Redirect to the list page
    return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation created successfully.');
    }

    /**
 * A private helper function to recursively save items.
 */
private function saveItems(array $items, int $quotationId, ?int $parentId): float
    {
        $total = 0;
        $sortOrder = 0;

        foreach ($items as $itemData) {
            // Check if essential data is present, skip if not (e.g., empty rows from Alpine)
            if (empty($itemData['description'])) {
                continue;
            }

            // Determine if it's a parent based on children existing in the data
            $isParent = !empty($itemData['children']);

            // Calculate subtotal for this item if it's NOT a parent
            $itemSubtotal = 0;
            if (!$isParent) {
                $quantity = $itemData['quantity'] ?? 0;
                $unit_price = $itemData['unit_price'] ?? 0;
                $itemSubtotal = $quantity * $unit_price;
            }

            // Create the QuotationItem
            $item = QuotationItem::create([
                'quotation_id' => $quotationId,
                'parent_id' => $parentId,
                // Add the AHS ID (use null if not set or empty)
                'unit_rate_analysis_id' => $itemData['unit_rate_analysis_id'] ?? null,
                'description' => $itemData['description'],
                'item_code' => $itemData['item_code'] ?? null,
                'uom' => $itemData['uom'] ?? null,
                // Use null for quantity/price if it's a parent
                'quantity' => $isParent ? null : ($itemData['quantity'] ?? 0),
                'unit_price' => $isParent ? null : ($itemData['unit_price'] ?? 0),
                'subtotal' => $itemSubtotal, // Calculated only for line items initially
                'sort_order' => $sortOrder++,
            ]);

            // If this item has children, save them recursively
            $childrenTotal = 0;
            if ($isParent) {
                $childrenTotal = $this->saveItems($itemData['children'], $quotationId, $item->id);
            }

            // If an item has children, its subtotal is the sum of its children.
            // Update the item's subtotal after children are processed.
            if ($childrenTotal > 0) {
                $item->subtotal = $childrenTotal;
                $item->save(); // Save the updated subtotal for the parent
                $total += $childrenTotal; // Add children's total to the current level's total
            } elseif (!$isParent) {
                // Only add line item subtotals directly
                $total += $itemSubtotal;
            }
            // Parent items without children contribute 0 to the total directly
        }

        return $total;
    }

    /**
     * Display the specified resource.
     */
    public function show(Quotation $quotation)
    {
        $quotation->load('client', 'items.children');

        return view('quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quotation $quotation)
    {
        /// Get all clients for the dropdown
        $clients = Client::orderBy('name')->get();

        $ahsLibrary = UnitRateAnalysis::orderBy('code')->get();
        // Load ALL items as a flat list. Our helper function will build the tree.
        $quotation->load('allItems'); // <-- FIX: Changed from 'allItems.children'

        // We need to re-format the flat "allItems" into the same tree
        // structure our Alpine component expects.
        $itemsTree = $this->buildItemTree($quotation->allItems);

        return view('quotations.edit', compact('quotation', 'clients', 'itemsTree'));
    }

    /**
     * Helper function to build a nested tree for the edit form.
     */
    private function buildItemTree($items, $parentId = null)
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildItemTree($items, $item->id);
                if (!empty($children)) {
                    $item->children = $children;
                } else {
                    $item->children = []; // Ensure children is always an array
                }
                
                // Add 'open: true' for the Alpine UI
                $item->open = true; 
                
                $branch[] = $item;
            }
        }

        return $branch;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quotation $quotation)
    {
        // 1. Validate the main quotation fields
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_name' => 'required|string|max:255',
            'date' => 'required|date',
            'items_json' => 'required|json', // Validate that it's a valid JSON string
        ]);

        // Decode the JSON string into an array
        $itemsArray = json_decode($validatedData['items_json'], true);

        $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
        'items' => 'required|array|min:1',
        'items.*.description' => 'required|string|max:255',
        'items.*.item_code' => 'nullable|string|max:50',
        'items.*.uom' => 'nullable|string|max:50',
        'items.*.quantity' => ['nullable', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($itemsArray) { /* ... same logic ... */ }],
        'items.*.unit_price' => ['nullable', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($itemsArray) { /* ... same logic ... */ }],
        'items.*.unit_rate_analysis_id' => 'nullable|exists:unit_rate_analyses,id',
        'items.*.children' => 'nullable|array',
    ]);

    if ($itemsValidator->fails()) {
        return back()->withInput()->withErrors($itemsValidator);
    }


        $grandTotal = 0;

        try {
            // 2. Start a database transaction
            DB::beginTransaction();

            // 3. Update the main Quotation details
            $quotation->update([
                'client_id' => $validatedData['client_id'],
                'project_name' => $validatedData['project_name'],
                'date' => $validatedData['date'],
            ]);

            // 4. THIS IS THE KEY: Delete all old items
            // We use allItems() to ensure we get *all* items, not just root ones.
            $quotation->allItems()->delete(); 

            // 5. Call our existing recursive function to save the new items
            $grandTotal = $this->saveItems($itemsArray, $quotation->id, null);

            // 6. Now update the quotation's total_estimate
            $quotation->total_estimate = $grandTotal;
            $quotation->save();

            // 7. Commit the transaction
            DB::commit();

        } catch (\Exception $e) {
            // 8. If anything went wrong, roll back
            DB::rollBack();
            return back()->withInput()->withErrors('Error updating quotation: ' . $e->getMessage());
        }

        // 9. Redirect back to the "show" page with a success message
        return redirect()->route('quotations.show', $quotation)
                         ->with('success', 'Quotation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quotation $quotation)
    {
        //
    }

    /**
     * Update the status of the specified quotation.
     */
    // vvv ADD THIS ENTIRE METHOD vvv
    public function updateStatus(Request $request, Quotation $quotation)
    {
        // 1. Validate the incoming status
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['sent', 'approved', 'rejected', 'draft']),
            ],
        ]);

        $newStatus = $validated['status'];
        $message = 'Status updated successfully.';

        try {
            DB::beginTransaction();

            // 2. THE CORE LOGIC: Check if we are approving
            // We also check that a project doesn't already exist to prevent duplicates
            if ($newStatus == 'approved' && !$quotation->project) {
                
                // 3. Create the new Project
                Project::create([
                    'quotation_id' => $quotation->id,
                    'client_id' => $quotation->client_id,
                    'total_budget' => $quotation->total_estimate,
                    'status' => 'initiated', // Default project status
                    // 'project_code' will be auto-generated by the model
                ]);
                
                $message = 'Quotation approved and project created!';
            }
            
            // 4. Update the quotation status
            $quotation->status = $newStatus;
            $quotation->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // Return with a specific error
            return back()->withErrors('Error updating status: ' . $e->getMessage());
        }

        // 5. Redirect back to the "show" page
        return redirect()->route('quotations.show', $quotation)->with('success', $message);
    }
}
