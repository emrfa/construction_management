<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Client;
use App\Models\QuotationItem;
use App\Models\Project;
use App\Models\UnitRateAnalysis;
use App\Models\WorkItem;
use App\Models\WorkType;
use App\Models\StockLocation;

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
   public function index(Request $request)
    {
        // Get data for filter dropdowns
        $clients = Client::orderBy('name')->get();
        $statuses = ['draft', 'sent', 'approved', 'rejected'];

        // Start query
        $query = Quotation::with('client')->latest();

        // [UPDATED] unified search: Quote #, Project Name, OR Client Name
        $query->when($request->search, function ($q, $search) {
            return $q->where(function ($subQ) use ($search) {
                $subQ->where('quotation_no', 'like', "%{$search}%")
                     ->orWhere('project_name', 'like', "%{$search}%")
                     ->orWhereHas('client', function ($clientQ) use ($search) {
                         $clientQ->where('name', 'like', "%{$search}%");
                     });
            });
        });

        // Apply status filter
        $query->when($request->status, function ($q, $status) {
            return $q->where('status', $status);
        });

        // Apply date range filter
        $query->when($request->date_from, function ($q, $date_from) {
            return $q->where('date', '>=', $date_from);
        });
        $query->when($request->date_to, function ($q, $date_to) {
            return $q->where('date', '<=', $date_to);
        });

        // Paginate results
        $quotations = $query->paginate(15)->appends($request->query());

        return view('quotations.index', compact('quotations', 'clients', 'statuses'));
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
            'workItems.unitRateAnalyses', // For "Group" types
            'unitRateAnalyses'            // For direct "Task" types
        ])->orderBy('name')->get();
        
        // 4. For the new "Pull Work Item" dropdown (with AHS details)
        $workItemsLibrary_json = \App\Models\WorkItem::with('unitRateAnalyses')->orderBy('name')->get();
        
        // 5. For the Alpine 'linkAHS' function
        $ahsJsonData = $ahsLibrary->mapWithKeys(fn($ahs) => [$ahs->id => [
            'code' => $ahs->code,
            'name' => $ahs->name,
            'unit' => $ahs->unit,
            'cost' => $ahs->total_cost
        ]]);
        
        // 6. For repopulating the form on a validation error
        $oldItemsArray = old('items_json') ? json_decode(old('items_json'), true) : [];

        // 7. Pass all data to the view
        return view('quotations.create', [
            'clients' => $clients,
            'ahsLibrary' => $ahsLibrary,
            'ahsJsonData' => $ahsJsonData,
            'workTypesLibrary_json' => $workTypesLibrary_json,
            'oldItemsArray' => $oldItemsArray,
            'workItemsLibrary_json' => $workItemsLibrary_json
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

        try {
        $this->validateHierarchy($itemsArray);
    } catch (\Exception $e) {
        return back()->withInput()->withErrors(['items_json' => $e->getMessage()]);
    }

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
        $quotation->disableLogging();
        $quotation->total_estimate = $grandTotal;
        $quotation->save();
        $quotation->enableLogging();

        // 6. Commit the transaction
        DB::commit();

    } catch (\Exception $e) {
        // 7. If anything went wrong, roll back
        DB::rollBack();
        if (isset($quotation)) {
                $quotation->enableLogging();
            }
        // Optional: return with a specific error message
        return back()->withInput()->withErrors('Error saving quotation: ' . $e->getMessage());
    }

    // 8. Redirect to the list page
    return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation created successfully.');
    }

    private function validateHierarchy(array $items, ?string $parentType = null)
{
    foreach ($items as $index => $item) {
        $type = $item['type'] ?? null;

        // Basic type presence
        if (!$type) {
            throw new \Exception("Item at index {$index} is missing 'type'.");
        }

        // Parent-child rules
        if ($parentType === 'work_item' && $type !== 'ahs') {
            throw new \Exception("Work Item may only contain AHS children (illegal child type '{$type}').");
        }
        if ($parentType === 'ahs') {
            throw new \Exception("AHS cannot have children.");
        }
        if ($type === 'sub_project' && $parentType !== null) {
            throw new \Exception("Sub Project must be a root-level item.");
        }
        if ($type === 'work_type' && !in_array($parentType, [null, 'sub_project'])) {
            throw new \Exception("Work Type can only be root-level or under a Sub Project.");
        }
        if ($type === 'work_item' && $parentType !== 'work_type') {
            throw new \Exception("Work Item must be under a Work Type.");
        }

        // Recursively validate children
        if (!empty($item['children']) && is_array($item['children'])) {
            $this->validateHierarchy($item['children'], $type);
        } else {
            // if parent type expects children ensure array is present for parents
            if (in_array($type, ['sub_project','work_type','work_item']) && empty($item['children'])) {
                // It's OK for a Work Type or Work Item to be empty (manual add), so we don't force children.
                // But ensure types like 'ahs' don't have children
                if ($type === 'ahs' && !empty($item['children'])) {
                    throw new \Exception("AHS cannot have children.");
                }
            }
        }
    }
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
        $quotation->load([
        'client', 
        'items.children', 
        'activities' => fn($query) => $query->latest(), 
        'activities.causer'
    ]);

        return view('quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quotation $quotation)
    {
        // 1. Get all clients for the dropdown
        $clients = Client::orderBy('name')->get();
        
        // --- THIS IS THE NEW LOGIC (COPIED FROM 'create') ---

        // 2. For the manual AHS select dropdowns
        $ahsLibrary = UnitRateAnalysis::orderBy('name')->get();

        // 3. For the "Pull Work Type" dropdown
        $workTypesLibrary_json = WorkType::with([
            'workItems.unitRateAnalyses',
            'unitRateAnalyses'
        ])->orderBy('name')->get();

        // 4. For the "Pull Work Item" dropdown
        $workItemsLibrary_json = \App\Models\WorkItem::with('unitRateAnalyses')->orderBy('name')->get();

        // 5. For the Alpine 'linkAHS' function (THIS FIXES YOUR ERROR)
        $ahsJsonData = $ahsLibrary->mapWithKeys(fn($ahs) => [$ahs->id => [
            'code' => $ahs->code,
            'name' => $ahs->name,
            'uom' => $ahs->unit,
            'unit_price' => $ahs->total_cost,
        ]])->toJson();
        
        // --- END OF NEW LOGIC ---

        // 6. Get the existing items for this quotation
        $quotation->load('allItems');
        $itemsTree = $this->buildItemTree($quotation->allItems);
        
        // 7. Pass all data to the view
        return view('quotations.edit', [
            'quotation' => $quotation,
            'clients' => $clients,
            'ahsLibrary' => $ahsLibrary,
            'workTypesLibrary_json' => $workTypesLibrary_json,
            'workItemsLibrary_json' => $workItemsLibrary_json,
            'ahsJsonData' => $ahsJsonData,
            'oldItemsArray' => $itemsTree, // Pass the items tree
        ]);
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
        try {
                $this->validateHierarchy($itemsArray);
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['items_json' => $e->getMessage()]);
            }

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
    public function updateStatus(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['sent', 'approved', 'rejected', 'draft']),
            ],
        ]);

        $newStatus = $validated['status'];
        $message = 'Status updated successfully.';

        $quotation->disableLogging();

        try {
            DB::beginTransaction();

            if ($newStatus == 'approved' && !$quotation->project) {

                // 1. Create the Project
                $project = Project::create([
                    'quotation_id' => $quotation->id,
                    'client_id' => $quotation->client_id,
                    'total_budget' => $quotation->total_estimate,
                    'status' => 'initiated',
                ]);

                // 2. Create the dedicated Stock Location for this Project
                StockLocation::create([
                    'code' => 'SITE-' . $project->project_code,
                    'name' => $project->quotation->project_name . ' Site',
                    'type' => 'site',
                    'project_id' => $project->id,
                    'is_active' => true,
                ]);

                $message = 'Quotation approved and project (with stock location) created!';
                activity()
                   ->on($quotation)
                   ->by(auth()->user())
                   ->log('Approved');

            } elseif ($newStatus == 'sent') {
                activity()->on($quotation)->by(auth()->user())->log('Sent');
            } elseif ($newStatus == 'rejected') {
                activity()->on($quotation)->by(auth()->user())->log('Rejected');
            }

            $quotation->status = $newStatus;
            $quotation->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $quotation->enableLogging();
            return back()->withErrors('Error updating status: ' . $e->getMessage());
        }
        
        return redirect()->route('quotations.show', $quotation)->with('success', $message);
    }
}
