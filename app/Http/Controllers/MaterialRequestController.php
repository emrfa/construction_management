<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use App\Models\Project;
use App\Models\QuotationItem;
use App\Models\InventoryItem;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaterialRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
        {$projectId = $request->query('project_id');
        if (!$projectId) {
            // Handle case where project_id is missing, maybe redirect back or show error
             return redirect()->route('projects.index')->with('error', 'Project ID is required to create a material request.');
        }

        // Eager load necessary relationships efficiently
        $project = Project::with([
            // Get quotation items that ARE line items (no children/parent_id=null check insufficient with deep hierarchy)
            // AND are linked to an AHS which HAS materials linked to inventory items.
            'quotation.allItems.unitRateAnalysis.materials.inventoryItem'
        ])->findOrFail($projectId);

        // Check if project has a quotation
        if (!$project->quotation) {
             return back()->with('error', 'Project does not have an associated quotation.');
        }

        $wbsMaterials = [];

        // Iterate through ALL items in the quotation
        foreach ($project->quotation->allItems as $item) {
            // We only care about line items (no children based on structure)
            // AND items linked to an AHS that has materials defined
            if ($item->children->isEmpty() // Ensure it's a leaf node in the WBS
                && $item->unitRateAnalysis // Ensure it's linked to AHS
                && $item->unitRateAnalysis->materials->isNotEmpty()) // Ensure AHS has materials
            {
                // Map the materials for this specific WBS item
                $materialsData = $item->unitRateAnalysis->materials->mapWithKeys(function ($mat) use ($item) {
                     // Safety check if inventoryItem relationship didn't load properly
                    if (!$mat->inventoryItem) {
                        \Log::warning("InventoryItem missing for UnitRateMaterial ID {$mat->id} on QuotationItem ID {$item->id}");
                        return []; // Return empty array element to be filtered out
                    }
                    return [$mat->inventory_item_id => [
                        'name' => $mat->inventoryItem->item_name,
                        'uom' => $mat->inventoryItem->uom,
                        'code' => $mat->inventoryItem->item_code,
                        // Calculate potential required qty (optional display, not used for selection)
                        'required_qty' => ($item->quantity ?? 0) * ($mat->coefficient ?? 0),
                    ]];
                })->filter(); // Remove any empty results from safety check

                // Only add WBS item if it actually has materials defined in its AHS
                if ($materialsData->isNotEmpty()) {
                    $wbsMaterials[$item->id] = [
                        'description' => $item->description, // Description from the quotation item
                        'materials' => $materialsData->all(), // Array of materials for this WBS item
                    ];
                }
            }
        }

        // Sort WBS items alphabetically by description for the dropdown
        uasort($wbsMaterials, function ($a, $b) {
            return strcmp($a['description'], $b['description']);
        });

        // Debugging: Uncomment to check data before sending to view
        // dd($project, $wbsMaterials);

        return view('material-requests.create', compact('project', 'wbsMaterials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate header and basic items structure
        $validatedData = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'request_date' => 'required|date',
            'required_date' => 'nullable|date|after_or_equal:request_date',
            'notes' => 'nullable|string',
            'items_json' => 'required|json', // Validate the raw JSON input
        ]);

        // Decode the JSON items
        $itemsArray = json_decode($validatedData['items_json'], true);

        // 2. Deeper validation on the decoded items array
        $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
            'items' => 'required|array|min:1',
            'items.*.quotation_item_id' => 'required|exists:quotation_items,id', // WBS link is required
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id', // Material is required
            'items.*.quantity_requested' => 'required|numeric|min:0.01',
        ]);

        if ($itemsValidator->fails()) {
            // Redirect back with JSON items re-encoded for the 'old()' helper
            return back()
                   ->withInput($request->except('items_json') + ['items_json' => $validatedData['items_json']])
                   ->withErrors($itemsValidator);
        }
        $validatedItems = $itemsValidator->validated()['items']; // Get validated items array


        try {
            DB::beginTransaction();

            // 3. Create the Material Request Header
            $materialRequest = MaterialRequest::create([
                'project_id' => $validatedData['project_id'],
                'requested_by_user_id' => Auth::id(),
                'request_date' => $validatedData['request_date'],
                'required_date' => $validatedData['required_date'],
                'notes' => $validatedData['notes'],
                'status' => 'pending_approval', // Default status for new requests
            ]);

            // 4. Create Material Request Items
            foreach ($validatedItems as $itemData) {
                MaterialRequestItem::create([
                    'material_request_id' => $materialRequest->id,
                    'quotation_item_id' => $itemData['quotation_item_id'],
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity_requested' => $itemData['quantity_requested'],
                    'quantity_fulfilled' => 0, // Starts at 0
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            // Log::error("Error creating material request: " . $e->getMessage());
            return back()
                   ->withInput($request->except('items_json') + ['items_json' => $validatedData['items_json']]) // Preserve input
                   ->with('error', 'Error creating material request. Please try again.');
                   // Or show detailed error in dev: ->with('error', 'Error: ' . $e->getMessage());
        }

        // 5. Redirect to the Project Dashboard
        return redirect()->route('projects.show', $validatedData['project_id'])
                         ->with('success', 'Material request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialRequest $materialRequest)
    {
        // Load necessary relationships
        $materialRequest->load([
            'project.quotation', // For project name/code
            'requester',         // User who requested
            'approver',          // User who approved
            'items.inventoryItem', // Material details for each item
            'items.quotationItem', // WBS item linked
            'purchaseOrders.supplier',
            'internalTransfers.sourceLocation', // Load transfers and their source
            'internalTransfers.destinationLocation',
        ]);

        return view('material-requests.show', compact('materialRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MaterialRequest $materialRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaterialRequest $materialRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialRequest $materialRequest)
    {
        //
    }

    /**
 * Update the status of the material request (Approve/Reject).
 */
public function updateStatus(Request $request, MaterialRequest $materialRequest)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['approved', 'rejected', 'pending_approval']),
            ],
        ]);

        $newStatus = $validated['status'];
        $message = 'Material request status updated.';

        // Prevent invalid transitions (e.g., approving an already rejected/fulfilled request)
        if (!in_array($materialRequest->status, ['pending_approval', 'rejected'])) {
            if ($newStatus !== $materialRequest->status) { // Allow clicking same status again harmlessly
                return back()->with('error', 'Request is not in a state that can be ' . $newStatus . '.');
            }
        }
        // Prevent approving a rejected request directly (must re-open first)
        if ($materialRequest->status === 'rejected' && $newStatus === 'approved') {
            return back()->with('error', 'Rejected requests must be re-opened first.');
        }

        try {
            DB::beginTransaction();

            $materialRequest->status = $newStatus;
            $materialRequest->approved_by_user_id = ($newStatus == 'approved') ? Auth::id() : null; // Record approver
            // Maybe add approval_date field later

            // --- TRIGGER PO CREATION ON APPROVAL ---
            // if ($newStatus == 'approved') {
            //     $this->createPurchaseOrdersIfNeeded($materialRequest); // Call helper function
            //     $message = 'Material request approved. Check stock or generated POs.';
            // }
            // --- END TRIGGER ---

            $materialRequest->save();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating material request status: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Error updating status: ' . $e->getMessage());
        }

        return redirect()->route('material-requests.show', $materialRequest)->with('success', $message);
    }

    /**
 * Helper Function: Check stock and create draft POs for approved items.
 * (Basic implementation - needs refinement for stock checking & grouping)
 */
private function createPurchaseOrdersIfNeeded(MaterialRequest $materialRequest)
    {
        $materialRequest->load('items.inventoryItem'); // Load items and materials

        // --- Basic Stock Check & Grouping Logic (Needs Refinement) ---
        // TODO: Implement actual stock check: InventoryItem::find($itemId)->quantity_on_hand
        // TODO: Group items by Supplier to create fewer POs

        $itemsToOrder = [];
        foreach ($materialRequest->items as $item) {
            $needed = $item->quantity_requested - $item->quantity_fulfilled;
            if ($needed <= 0) continue; // Skip if already fulfilled

            // --- Placeholder: Assume we need to order everything for now ---
            $stockAvailable = 0; // Replace with actual stock check result
            // --- End Placeholder ---

            if ($needed > $stockAvailable) {
                // Determine quantity to order (e.g., full needed amount)
                $qtyToOrder = $needed - $stockAvailable; // Order the shortfall

                // TODO: Determine Supplier (Needs logic, maybe from InventoryItem master?)
                $supplierId = null; // Replace with actual supplier ID
                // For now, group all under a generic 'Order' key, later group by supplierId
                $groupKey = 'order'; // Replace with $supplierId

                if ($supplierId === null) {
                    \Log::warning("No supplier defined for Inventory Item ID: {$item->inventory_item_id}. Skipping PO creation for this item.");
                    continue; // Skip if no supplier can be determined
                }


                if (!isset($itemsToOrder[$groupKey])) {
                    $itemsToOrder[$groupKey] = [
                        'supplier_id' => $supplierId,
                        'items' => []
                    ];
                }
                $itemsToOrder[$groupKey]['items'][] = [
                    'inventory_item_id' => $item->inventory_item_id,
                    'quantity_ordered' => $qtyToOrder,
                    'unit_cost' => 0, // TODO: Get default/last cost? Or leave 0 for manual entry?
                ];

                // Optionally update quantity_fulfilled (partially, if some came from stock)
                // $item->quantity_fulfilled += $stockAvailable;
                // $item->save();
            } else {
                // Fulfill directly from stock (Needs Stock Issue logic)
                // TODO: Create negative stock transaction, update item->quantity_fulfilled
            }
        }

        // --- Create Draft Purchase Orders ---
        foreach ($itemsToOrder as $orderData) {
            if (empty($orderData['items'])) continue; // Skip if no items for this PO

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $orderData['supplier_id'],
                'order_date' => now()->toDateString(),
                'status' => 'draft', // Create as Draft
                'total_amount' => 0, // Will be calculated when PO items are added/edited
                'project_id' => $materialRequest->project_id,
                // TODO: Link PO back to MaterialRequest? Add material_request_id field?
            ]);

            $totalAmount = 0;
            foreach ($orderData['items'] as $poItemData) {
                $subtotal = $poItemData['quantity_ordered'] * $poItemData['unit_cost'];
                $purchaseOrder->items()->create([
                    'inventory_item_id' => $poItemData['inventory_item_id'],
                    'quantity_ordered' => $poItemData['quantity_ordered'],
                    'unit_cost' => $poItemData['unit_cost'],
                    'subtotal' => $subtotal,
                ]);
                $totalAmount += $subtotal;
            }
            // Update draft PO total (can be recalculated later if costs change)
            $purchaseOrder->total_amount = $totalAmount;
            $purchaseOrder->save();
        }

        // Update Material Request status if fully or partially handled by POs/Stock
        // TODO: Add logic here to set status to 'partially_fulfilled' or 'fulfilled'
        // based on whether all item quantities are covered by stock issues or generated POs.
        // For now, it stays 'approved'.
    }

    /**
 * Create a draft Purchase Order based on an approved Material Request.
 */
public function createPurchaseOrder(MaterialRequest $materialRequest)
    {
        // 1. Validation: Ensure request is approved and needs fulfillment
        if ($materialRequest->status !== 'approved' && $materialRequest->status !== 'partially_fulfilled') {
            return back()->with('error', 'Only approved material requests can generate a PO.');
        }

        $materialRequest->load('items.inventoryItem'); // Load items needed

        $itemsToOrder = [];
        foreach ($materialRequest->items as $item) {
            $needed = $item->quantity_requested - $item->quantity_fulfilled;
            if ($needed > 0) {
                // Prepare item data for the PO
                $itemsToOrder[] = [
                    'inventory_item_id' => $item->inventory_item_id,
                    'material_request_item_id' => $item->id, // Link back to the request item
                    'quantity_ordered' => $needed, // Order the remaining needed quantity
                    'unit_cost' => 0, // Default cost to 0, user fills in draft PO
                ];
            }
        }

        if (empty($itemsToOrder)) {
            return back()->with('info', 'All items on this request are already fulfilled.');
        }

        try {
            DB::beginTransaction();

            // 2. Create the Draft Purchase Order Header
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => null, // No supplier selected yet
                'material_request_id' => $materialRequest->id, // Link back to the request
                'order_date' => now()->toDateString(),
                'status' => 'draft',
                'total_amount' => 0, // Will be calculated/updated in edit
                // Add project_id to PO if needed (requires migration + model update)
                'project_id' => $materialRequest->project_id,
            ]);

            // 3. Create Purchase Order Items
            foreach ($itemsToOrder as $poItemData) {
                $subtotal = $poItemData['quantity_ordered'] * $poItemData['unit_cost']; // Will be 0 initially
                $purchaseOrder->items()->create([
                    'inventory_item_id' => $poItemData['inventory_item_id'],
                    'material_request_item_id' => $poItemData['material_request_item_id'],
                    'quantity_ordered' => $poItemData['quantity_ordered'],
                    'unit_cost' => $poItemData['unit_cost'],
                    'subtotal' => $subtotal,
                ]);
                // We don't update the total amount here, user does it in edit
            }

            // Optional: Update Material Request status (e.g., to 'processing' or similar)
            // $materialRequest->status = 'processing';
            // $materialRequest->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error creating PO from Material Request: " . $e->getMessage());
            return back()->with('error', 'Error creating Purchase Order: ' . $e->getMessage());
        }

        // 4. Redirect to the EDIT page of the new draft PO
        return redirect()->route('purchase-orders.edit', $purchaseOrder)
                        ->with('success', 'Draft Purchase Order created. Please select supplier and confirm details.');
    }

    public function createTransfer(Request $request, MaterialRequest $materialRequest)
    {
        $validated = $request->validate([
            'source_location_id' => 'required|exists:stock_locations,id',
        ]);

        // Check if user has access to source
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasAccessToLocation($validated['source_location_id'])) {
            return back()->with('error', 'You do not have permission to transfer from the selected source location.');
        }

        try {
            DB::beginTransaction();

            // We need a destination. Does the Project have a location?
            // StockLocation has project_id.
            $destLocation = \App\Models\StockLocation::where('project_id', $materialRequest->project_id)->first();
            
            if (!$destLocation) {
                throw new \Exception("No Stock Location found for this Project. Please create one first.");
            }

            $transfer = \App\Models\InternalTransfer::create([
                'transfer_number' => 'TRF-' . strtoupper(uniqid()),
                'source_location_id' => $validated['source_location_id'],
                'destination_location_id' => $destLocation->id,
                'status' => 'draft',
                'created_by_user_id' => Auth::id(),
                'material_request_id' => $materialRequest->id,
                'notes' => 'Generated from Material Request ' . $materialRequest->request_number,
            ]);

            foreach ($materialRequest->items as $mrItem) {
                // Map MR Item (Quotation Item) to Inventory Item
                $inventoryItemId = $mrItem->quotationItem->inventory_item_id ?? null;
                
                if ($inventoryItemId) {
                    \App\Models\InternalTransferItem::create([
                        'internal_transfer_id' => $transfer->id,
                        'inventory_item_id' => $inventoryItemId,
                        'quantity_requested' => $mrItem->quantity, // Request full quantity initially
                        'quantity_shipped' => 0,
                    ]);
                }
            }

            DB::commit();
            
            return redirect()->route('internal-transfers.show', $transfer)
                             ->with('success', 'Draft Transfer created from Material Request.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating transfer: ' . $e->getMessage());
        }
    }
}
