<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitRateAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class QuotationCalculatorController extends Controller
{
    /**
     * Receive a list of AHS IDs.
     * Return a list of ALL unique components (Materials, Labor, Equipment) used in them.
     */
    public function getProjectResources(Request $request)
    {
        $ahsIds = $request->input('ahs_ids', []);
        
        // Eager load everything needed
        $analyses = UnitRateAnalysis::with([
            'materials.inventoryItem', 
            'labors.laborRate', 
            'equipments.equipment'
        ])->whereIn('id', $ahsIds)->get();

        $materials = collect();
        $labors = collect();
        $equipments = collect();

        foreach ($analyses as $ahs) {
            // 1. Flatten Materials
            foreach ($ahs->materials as $mat) {
                if ($mat->inventoryItem) {
                    
                    // [FIX] Logic: Try Master Price first. If 0, use the Snapshot price from the AHS.
                    $price = $mat->inventoryItem->base_purchase_price;
                    if (empty($price) || $price == 0) {
                        $price = $mat->unit_cost;
                    }

                    // We key by ID to ensure uniqueness
                    $materials->put($mat->inventory_item_id, [
                        'id' => $mat->inventory_item_id,
                        'name' => $mat->inventoryItem->item_name,
                        'code' => $mat->inventoryItem->item_code,
                        'uom' => $mat->inventoryItem->uom,
                        'default_price' => (float) $price, // Ensure it is a float
                        'type' => 'material'
                    ]);
                }
            }

            // 2. Flatten Labors
            foreach ($ahs->labors as $lab) {
                if ($lab->laborRate) {
                    
                    // [FIX] Logic: Try Master Rate first. If 0, use Snapshot rate.
                    $price = $lab->laborRate->rate;
                    if (empty($price) || $price == 0) {
                        $price = $lab->rate;
                    }

                    $labors->put($lab->labor_rate_id, [
                        'id' => $lab->labor_rate_id,
                        'name' => $lab->laborRate->labor_type,
                        'code' => '-', 
                        'uom' => $lab->laborRate->unit,
                        'default_price' => (float) $price,
                        'type' => 'labor'
                    ]);
                }
            }

            // 3. Flatten Equipment
            foreach ($ahs->equipments as $eq) {
                if ($eq->equipment) {
                    
                    // [FIX] Logic: Determine default price based on status
                    $price = ($eq->equipment->status === 'rented') 
                        ? $eq->equipment->rental_rate 
                        : $eq->equipment->base_rental_rate;

                    // Fallback to AHS snapshot if master is zero
                    if (empty($price) || $price == 0) {
                        $price = $eq->cost_rate;
                    }

                    $equipments->put($eq->equipment_id, [
                        'id' => $eq->equipment_id,
                        'name' => $eq->equipment->name,
                        'code' => $eq->equipment->identifier,
                        'uom' => $eq->equipment->rental_rate_unit ?? $eq->equipment->base_rental_rate_unit ?? 'unit',
                        'default_price' => (float) $price,
                        'type' => 'equipment'
                    ]);
                }
            }
        }

        return response()->json([
            'materials' => $materials->values(),
            'labors' => $labors->values(),
            'equipments' => $equipments->values(),
        ]);
    }

    /**
     * Receive AHS IDs and a list of Price Overrides.
     * Return the NEW unit prices for those AHS items.
     */
    public function recalculateAhsPrices(Request $request)
    {
        $ahsIds = $request->input('ahs_ids', []);
        $overrides = $request->input('overrides', []); 

        $analyses = UnitRateAnalysis::with(['materials', 'labors', 'equipments'])->whereIn('id', $ahsIds)->get();
        
        $newPrices = [];

        foreach ($analyses as $ahs) {
            $total = 0;

            // 1. Calculate Material Cost
            foreach ($ahs->materials as $mat) {
                // Check if there is an override, otherwise use the unit_cost from the AHS snapshot
                $price = $overrides['material'][$mat->inventory_item_id] ?? $mat->unit_cost;
                $total += $mat->coefficient * $price;
            }

            // 2. Calculate Labor Cost
            foreach ($ahs->labors as $lab) {
                $price = $overrides['labor'][$lab->labor_rate_id] ?? $lab->rate;
                $total += $lab->coefficient * $price;
            }

            // 3. Calculate Equipment Cost
            foreach ($ahs->equipments as $eq) {
                $price = $overrides['equipment'][$eq->equipment_id] ?? $eq->cost_rate;
                $total += $eq->coefficient * $price;
            }

            // 4. Add Overhead
            $grandTotal = $total * (1 + ($ahs->overhead_profit_percentage / 100));

            $newPrices[$ahs->id] = round($grandTotal, 2);
        }

        return response()->json($newPrices);
    }
}