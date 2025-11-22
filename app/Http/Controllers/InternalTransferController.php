<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\InternalTransfer;
use App\Models\InternalTransferItem;
use App\Models\StockLocation;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternalTransferController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = InternalTransfer::with(['sourceLocation', 'destinationLocation', 'createdBy'])->latest();

        // Filter by User's Location Access (Source OR Destination)
        // If user has NO assigned locations, maybe show nothing or all? 
        // Assuming strict: only show if user has access to source OR dest.
        if (!$user->hasRole('admin')) { // Assuming 'admin' bypasses
             $userLocationIds = $user->stockLocations->pluck('id');
             $query->where(function($q) use ($userLocationIds) {
                 $q->whereIn('source_location_id', $userLocationIds)
                   ->orWhereIn('destination_location_id', $userLocationIds);
             });
        }

        $transfers = $query->paginate(20);
        return view('internal-transfers.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Source: Only locations the user has access to (unless admin)
        if ($user->hasRole('admin')) {
            $sourceLocations = StockLocation::where('is_active', true)->orderBy('name')->get();
        } else {
            $sourceLocations = $user->stockLocations()->where('is_active', true)->orderBy('name')->get();
        }

        // Destination: All active locations
        $destinationLocations = StockLocation::where('is_active', true)->orderBy('name')->get();
        
        $inventoryItems = InventoryItem::orderBy('item_name')->get();

        // Prefill Logic for Material Request
        $prefilledDestinationId = null;
        $prefilledItems = [];

        if ($request->has('material_request_id')) {
            $materialRequest = \App\Models\MaterialRequest::with(['items.inventoryItem', 'project.stockLocation'])->find($request->material_request_id);
            
            if ($materialRequest) {
                // 1. Determine Destination from Project
                // Assuming Project has a 'site' stock location linked
                $projectLocation = $materialRequest->project->stockLocation;
                if ($projectLocation) {
                    $prefilledDestinationId = $projectLocation->id;
                }

                // 2. Prefill Items
                foreach ($materialRequest->items as $mrItem) {
                    $remaining = $mrItem->quantity_requested - $mrItem->quantity_fulfilled;
                    if ($remaining > 0) {
                        $prefilledItems[] = [
                            'inventory_item_id' => $mrItem->inventory_item_id,
                            'quantity_requested' => $remaining,
                        ];
                    }
                }
            }
        }

        return view('internal-transfers.create', compact('sourceLocations', 'destinationLocations', 'inventoryItems', 'prefilledDestinationId', 'prefilledItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_location_id' => 'required|exists:stock_locations,id',
            'destination_location_id' => 'required|exists:stock_locations,id|different:source_location_id',
            'notes' => 'nullable|string',
            'items_json' => 'required|json',
        ]);

        // Permission Check: Can user send from this source?
        $user = Auth::user();
        if (!$user->hasRole('admin') && !$user->hasAccessToLocation($validated['source_location_id'])) {
            return back()->with('error', 'You do not have permission to transfer from the selected source location.');
        }

        $itemsArray = json_decode($validated['items_json'], true);
        $itemsValidator = \Illuminate\Support\Facades\Validator::make(['items' => $itemsArray], [
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_requested' => 'required|numeric|min:0.01',
        ]);

        if ($itemsValidator->fails()) {
            return back()->withInput()->withErrors($itemsValidator);
        }
        $validatedItems = $itemsValidator->validated()['items'];

        try {
            DB::beginTransaction();

            $transfer = InternalTransfer::create([
                'transfer_number' => 'TRF-' . strtoupper(uniqid()), // Simple ID generation
                'source_location_id' => $validated['source_location_id'],
                'destination_location_id' => $validated['destination_location_id'],
                'status' => 'draft', // Starts as Draft
                'created_by_user_id' => Auth::id(),
                'material_request_id' => $request->input('material_request_id'), // Link to Material Request
                'notes' => $validated['notes'],
            ]);

            foreach ($validatedItems as $item) {
                InternalTransferItem::create([
                    'internal_transfer_id' => $transfer->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_shipped' => 0,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating transfer: ' . $e->getMessage());
        }

        return redirect()->route('internal-transfers.show', $transfer)
                         ->with('success', 'Internal Transfer Request created successfully.');
    }

    public function show(InternalTransfer $internalTransfer)
    {
        $internalTransfer->load(['items.inventoryItem', 'sourceLocation', 'destinationLocation', 'shipments', 'createdBy']);
        return view('internal-transfers.show', compact('internalTransfer'));
    }
}
