<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\QuotationItem;
use App\Models\ProgressUpdate;
use App\Models\InventoryItem;
use App\Models\MaterialUsage;
use App\Models\StockTransaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressUpdateController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $project->load('quotation.allItems');

        $tasks = $project->quotation->allItems->filter(function ($item) {
            return $item->children->isEmpty();
        });

        // ADD THIS LINE to get inventory items
        $inventoryItems = InventoryItem::orderBy('item_name')->get();

        // Pass the new variable to the view
        return view('progress.create', compact('project', 'tasks', 'inventoryItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        // 1. Validate the main progress data AND the materials array
        $validated = $request->validate([
            'quotation_item_id' => 'required|exists:quotation_items,id',
            'date' => 'required|date',
            'percent_complete' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            // Validate materials array (optional, allows empty submissions)
            'materials' => 'nullable|array',
            'materials.*.inventory_item_id' => 'required_with:materials|exists:inventory_items,id', // Required if materials array is present
            'materials.*.quantity_used' => 'required_with:materials|numeric|min:0.01', // Required if materials array is present
        ]);

        try {
            DB::beginTransaction();

            // 2. Create the main Progress Update record
            $progressUpdate = ProgressUpdate::create([
                'quotation_item_id' => $validated['quotation_item_id'],
                'user_id' => Auth::id(),
                'date' => $validated['date'],
                'percent_complete' => $validated['percent_complete'],
                'notes' => $validated['notes'],
            ]);

            // 3. Process Materials Used (if any were submitted)
            if (!empty($validated['materials'])) {
                foreach ($validated['materials'] as $materialData) {
                    // Ensure both fields are present for a material entry
                    if (empty($materialData['inventory_item_id']) || empty($materialData['quantity_used'])) {
                        continue; // Skip incomplete material entries
                    }

                    $quantityUsed = (float) $materialData['quantity_used'];
                    $inventoryItemId = $materialData['inventory_item_id'];

                    // Optional: Get the current cost of the item for logging
                    // $item = InventoryItem::find($inventoryItemId);
                    // $currentCost = $item->latest_cost ?? 0; // Need to implement latest_cost logic later

                    // Create the Material Usage record
                    MaterialUsage::create([
                        'progress_update_id' => $progressUpdate->id,
                        'inventory_item_id' => $inventoryItemId,
                        'quantity_used' => $quantityUsed,
                        // 'unit_cost' => $currentCost, // Store cost if needed
                    ]);

                    // Create the Stock Transaction (Decrease Stock)
                    StockTransaction::create([
                        'inventory_item_id' => $inventoryItemId,
                        'quantity' => -$quantityUsed, // Negative quantity for stock out
                        // 'unit_cost' => $currentCost, // Store cost if needed
                        'sourceable_id' => $progressUpdate->id, // Link to the ProgressUpdate
                        'sourceable_type' => ProgressUpdate::class,
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error saving progress update: ' . $e->getMessage());
        }

        // 4. Redirect back to the project dashboard
        return redirect()->route('projects.show', $project)
                         ->with('success', 'Progress update and material usage saved successfully.');
    }

        /**
     * Display the progress history for a specific task.
     */
    public function history(QuotationItem $quotation_item)
    {
        // Load the associated project to build the "back" link
        $project = $quotation_item->quotation->project;

        // Load all progress updates, and for each update, get the user who made it
        $quotation_item->load('progressUpdates.user');

        return view('progress.history', compact('project', 'quotation_item'));
    }
}
