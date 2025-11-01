<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\QuotationItem;
use App\Models\ProgressUpdate;
use App\Models\InventoryItem;
use App\Models\MaterialUsage;
use App\Models\StockTransaction;
use App\Models\LaborRate;
use App\Models\LaborUsage;

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

        $inventoryItems = InventoryItem::orderBy('item_name')->get();
        $laborRates = LaborRate::orderBy('labor_type')->get();

        // Pass the new variable to the view
        return view('progress.create', compact('project', 'tasks', 'inventoryItems', 'laborRates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        // 1. Validate the main data, percentage, materials, AND labor
        $validated = $request->validate([
            'quotation_item_id' => 'required|exists:quotation_items,id',
            'date' => 'required|date',
            'percent_complete' => 'required|numeric|min:0|max:100', // <-- IT'S BACK
            'notes' => 'nullable|string',

            // Materials
            'materials' => 'nullable|array',
            'materials.*.inventory_item_id' => 'required_with:materials|exists:inventory_items,id',
            'materials.*.quantity_used' => 'required_with:materials|numeric|min:0.01',
            
            // Labor
            'labors' => 'nullable|array',
            'labors.*.labor_rate_id' => 'required_with:labors|exists:labor_rates,id',
            'labors.*.quantity_used' => 'required_with:labors|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // 2. Create the main Progress Update record (WITH percentage)
            $progressUpdate = ProgressUpdate::create([
                'quotation_item_id' => $validated['quotation_item_id'],
                'user_id' => Auth::id(),
                'date' => $validated['date'],
                'percent_complete' => $validated['percent_complete'], // <-- IT'S BACK
                'notes' => $validated['notes'],
            ]);

            // 3. Process Materials Used (if any)
            if (!empty($validated['materials'])) {
                foreach ($validated['materials'] as $material) {
                    $inventoryItemId = $material['inventory_item_id'];
                    $quantityUsed = $material['quantity_used'];
                    
                    $costSourceTransaction = StockTransaction::where('inventory_item_id', $inventoryItemId)
                        ->where('project_id', $project->id)
                        ->where('quantity', '>', 0)
                        ->latest('created_at')
                        ->first();
                    
                    $unitCost = $costSourceTransaction ? $costSourceTransaction->unit_cost : 0;

                    MaterialUsage::create([
                        'progress_update_id' => $progressUpdate->id,
                        'inventory_item_id' => $inventoryItemId,
                        'quantity_used' => $quantityUsed,
                        'unit_cost' => $unitCost,
                    ]);

                    StockTransaction::create([
                        'inventory_item_id' => $inventoryItemId,
                        'project_id' => $project->id,
                        'quantity' => -$quantityUsed,
                        'unit_cost' => $unitCost,
                        'sourceable_id' => $progressUpdate->id,
                        'sourceable_type' => ProgressUpdate::class,
                    ]);
                }
            }

            // 4. Process Labor Used (if any)
            if (!empty($validated['labors'])) {
                foreach ($validated['labors'] as $labor) {
                    $laborRateId = $labor['labor_rate_id'];
                    $quantityUsed = $labor['quantity_used'];
                    $laborRate = LaborRate::find($laborRateId);
                    
                    LaborUsage::create([
                        'progress_update_id' => $progressUpdate->id,
                        'labor_rate_id' => $laborRateId,
                        'quantity_used' => $quantityUsed,
                        'unit_cost' => $laborRate->rate,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Error saving progress: ' + $e->getMessage());
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Progress and costs logged successfully.');
    }
}
