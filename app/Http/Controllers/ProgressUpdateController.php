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
use App\Models\Equipment;
use App\Models\EquipmentUsage; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgressUpdateController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $project->load('quotation.allItems');

        // Get all "leaf" tasks
        $tasks = $project->quotation->allItems->filter(function ($item) {
            return $item->children->isEmpty();
        });

        $inventoryItems = InventoryItem::orderBy('item_name')->get();
        $laborRates = LaborRate::orderBy('labor_type')->get();
        
        // Get equipment that is ready for use (not in maintenance, disposed, or pending)
        $equipments = Equipment::whereIn('status', ['owned', 'rented'])
                                ->orderBy('name')
                                ->get();

        return view('progress.create', compact(
            'project', 
            'tasks', 
            'inventoryItems', 
            'laborRates',
            'equipments'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        // 1. Validate the main data, percentage, materials, labor, AND equipment
        $validated = $request->validate([
            'quotation_item_id' => 'required|exists:quotation_items,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'percent_complete' => 'required|numeric|min:0|max:100',

            // Materials
            'materials_json' => 'nullable|json',
            
            // Labor
            'labors_json' => 'nullable|json',

           
            'equipments_json' => 'nullable|json',
        ]);

        // Decode the JSON inputs
        $materials = json_decode($validated['materials_json'] ?? '[]', true);
        $labors = json_decode($validated['labors_json'] ?? '[]', true);
        $equipments = json_decode($validated['equipments_json'] ?? '[]', true);

        DB::beginTransaction();
        try {
            // 2. Create the main Progress Update record
            $progressUpdate = ProgressUpdate::create([
                'quotation_item_id' => $validated['quotation_item_id'],
                'date' => $validated['date'],
                'notes' => $validated['notes'],
                'user_id' => Auth::id(),
                'percent_complete' => $validated['percent_complete'],
            ]);

            // 3. Process Materials Used (if any)
            foreach ($materials as $material) {
                if (empty($material['id']) || empty($material['quantity'])) continue;

                $inventoryItemId = $material['id'];
                $quantityUsed = $material['quantity'];

                // Find the average cost of this item *for this project*
                $costSourceTransaction = StockTransaction::where('inventory_item_id', $inventoryItemId)
                    ->where('project_id', $project->id)
                    ->where('quantity', '>', 0) // Only look at stock-in
                    ->avg('unit_cost'); // Get average cost
                
                $unitCost = $costSourceTransaction ?? 0;

                MaterialUsage::create([
                    'progress_update_id' => $progressUpdate->id,
                    'inventory_item_id' => $inventoryItemId,
                    'quantity_used' => $quantityUsed,
                    'unit_cost' => $unitCost,
                ]);

                // Create negative stock transaction
                StockTransaction::create([
                    'inventory_item_id' => $inventoryItemId,
                    'project_id' => $project->id,
                    'quantity' => -$quantityUsed,
                    'unit_cost' => $unitCost,
                    'sourceable_type' => ProgressUpdate::class,
                    'sourceable_id' => $progressUpdate->id,
                ]);
            }

            // 4. Process Labor Used (if any)
            foreach ($labors as $labor) {
                if (empty($labor['id']) || empty($labor['quantity'])) continue;

                $laborRateId = $labor['id'];
                $quantityUsed = $labor['quantity'];
                
                $laborRate = LaborRate::find($laborRateId);
                if (!$laborRate) continue; // Skip if rate not found

                LaborUsage::create([
                    'progress_update_id' => $progressUpdate->id,
                    'labor_rate_id' => $laborRateId,
                    'quantity_used' => $quantityUsed,
                    'unit_cost' => $laborRate->rate, // Get cost from master
                ]);
            }

            // --- 5. Process Equipment Used (if any) ---
            foreach ($equipments as $equipment) {
                if (empty($equipment['id']) || empty($equipment['quantity']) || empty($equipment['unit'])) continue;

                $equipmentId = $equipment['id'];
                $quantityUsed = $equipment['quantity'];
                $unitUsed = $equipment['unit']; // "Hour", "Day", "Week", "Month"

                $equipmentMaster = Equipment::find($equipmentId);
                if (!$equipmentMaster) continue; // Skip if not found
                
                // Get the correct rate based on status
                $baseRate = ($equipmentMaster->status == 'rented') 
                                ? $equipmentMaster->rental_rate 
                                : $equipmentMaster->base_rental_rate;
                                
                $baseUnit = ($equipmentMaster->status == 'rented')
                                ? $equipmentMaster->rental_rate_unit
                                : $equipmentMaster->base_rental_rate_unit;
                
                if (empty($baseRate) || empty($baseUnit)) {
                     Log::warning("Equipment ID $equipmentId used on ProgressUpdate $progressUpdate->id has no base rate or unit.");
                     continue; // Skip if no rate is set
                }

                // Calculate the total cost using our helper
                $totalCost = $this->calculateEquipmentCost(
                    $quantityUsed, 
                    $unitUsed, 
                    $baseRate, 
                    $baseUnit
                );

                EquipmentUsage::create([
                    'progress_update_id' => $progressUpdate->id,
                    'equipment_id' => $equipmentId,
                    'quantity_used' => $quantityUsed,
                    'unit_used' => $unitUsed,
                    'total_cost' => $totalCost, 
                ]);
            }


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving progress: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->withErrors('Error saving progress: ' . $e->getMessage());
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Progress and costs logged successfully.');
    }

    /**
     * ---HELPER FUNCTION ---
     * * Calculates the total cost of equipment use with unit conversion.
     */
    private function calculateEquipmentCost($quantity, $unit, $baseRate, $baseUnit)
    {
        // Define conversion factors (to Hours)
        $factors = [
            'hour' => 1,
            'day' => 8,   // 1 Day = 8 Hours
            'week' => 40,  // 1 Week = 5 days * 8 hours
            'month' => 160, // 1 Month = 4 weeks * 40 hours
        ];

        // Normalize units to lowercase
        $unit = strtolower($unit);
        $baseUnit = strtolower($baseUnit);
        
        if (!isset($factors[$unit]) || !isset($factors[$baseUnit]) || $baseRate == 0) {
            return 0; // Cannot calculate if units are unknown or rate is zero
        }
        
        // 1. Convert the base rate to a "per hour" rate
        $ratePerHour = $baseRate / $factors[$baseUnit];

        // 2. Convert the quantity used into hours
        $quantityInHours = $quantity * $factors[$unit];

        // 3. Calculate total cost
        return $ratePerHour * $quantityInHours;
    }

    public function history(QuotationItem $quotation_item)
    {
        // Eager load all relationships needed for the view
        $quotation_item->load([
            'quotation.project', // Needed to get the $project
            'progressUpdates.user',
            'progressUpdates.materialUsages.inventoryItem.stockTransactions',
            'progressUpdates.laborUsages.laborRate',
            'progressUpdates.equipmentUsages.equipment'
        ]);
        
        // Get the project from the loaded relationship
        $project = $quotation_item->quotation->project;

        // Pass both variables to the view with the correct names
        return view('progress.history', [
            'project' => $project,
            'quotation_item' => $quotation_item 
        ]);
    }
}
