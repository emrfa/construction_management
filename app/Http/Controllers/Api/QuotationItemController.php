<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuotationItem;
use Illuminate\Http\Request;

class QuotationItemController extends Controller
{
    /**
     * Get detailed drill-down data for a specific quotation item/task
     */
    public function drillDown(QuotationItem $item)
    {
        // Load all necessary relationships
        $item->load([
            'progressUpdates' => function ($query) {
                $query->latest()->take(5); // Last 5 progress updates
            },
            'progressUpdates.materialUsages.inventoryItem',
            'progressUpdates.laborUsages.labor',
            'progressUpdates.equipmentUsages.equipment',
            'progressUpdates.user',
            'unitRateAnalysis.materials.inventoryItem',
            'unitRateAnalysis.labors.labor',
            'unitRateAnalysis.equipments.equipment'
        ]);

        // Calculate cost breakdown
        $budgetedCost = [
            'materials' => 0,
            'labor' => 0,
            'equipment' => 0,
            'total' => (float) $item->subtotal
        ];

        $actualCost = [
            'materials' => 0,
            'labor' => 0,
            'equipment' => 0,
            'total' => (float) $item->actual_cost
        ];

        // Get budgeted breakdown from AHS
        if ($item->unitRateAnalysis) {
            $budgetedCost['materials'] = $item->unitRateAnalysis->materials->sum(function ($material) use ($item) {
                return $material->coefficient * $material->inventoryItem->standard_cost * $item->quantity;
            });
            
            $budgetedCost['labor'] = $item->unitRateAnalysis->labors->sum(function ($labor) use ($item) {
                return $labor->coefficient * $labor->labor->wage_per_hour * $item->quantity;
            });
            
            $budgetedCost['equipment'] = $item->unitRateAnalysis->equipments->sum(function ($equipment) use ($item) {
                return $equipment->coefficient * $equipment->equipment->hourly_rate * $item->quantity;
            });
        }

        // Get actual costs from progress updates
        foreach ($item->progressUpdates as $update) {
            $actualCost['materials'] += $update->materialUsages->sum(function ($usage) {
                return $usage->quantity * $usage->inventoryItem->standard_cost;
            });
            
            $actualCost['labor'] += $update->laborUsages->sum(function ($usage) {
                return $usage->hours_used * $usage->labor->wage_per_hour;
            });
            
            $actualCost['equipment'] += $update->equipmentUsages->sum(function ($usage) {
                return $usage->hours_used * $usage->equipment->hourly_rate;
            });
        }

        // Progress timeline data (for chart)
        $progressHistory = $item->progressUpdates()->orderBy('update_date', 'asc')->get()->map(function ($update) {
            return [
                'date' => $update->update_date,
                'progress' => (float) $update->progress_percentage,
                'cumulative_progress' => (float) $update->progress_percentage, // This would need cumulative logic
                'user' => $update->user->name ?? 'Unknown',
                'notes' => $update->notes
            ];
        });

        // Recent updates with full details
        $recentUpdates = $item->progressUpdates->take(5)->map(function ($update) {
            return [
                'id' => $update->id,
                'date' => $update->update_date,
                'progress' => (float) $update->progress_percentage,
                'user' => $update->user->name ?? 'Unknown',
                'notes' => $update->notes,
                'materials_used' => $update->materialUsages->map(function ($usage) {
                    return [
                        'item' => $usage->inventoryItem->name,
                        'quantity' => $usage->quantity,
                        'uom' => $usage->inventoryItem->uom,
                        'cost' => $usage->quantity * $usage->inventoryItem->standard_cost
                    ];
                }),
                'labor_used' => $update->laborUsages->map(function ($usage) {
                    return [
                        'type' => $usage->labor->name,
                        'hours' => $usage->hours_used,
                        'cost' => $usage->hours_used * $usage->labor->wage_per_hour
                    ];
                }),
                'equipment_used' => $update->equipmentUsages->map(function ($usage) {
                    return [
                        'type' => $usage->equipment->name,
                        'hours' => $usage->hours_used,
                        'cost' => $usage->hours_used * $usage->equipment->hourly_rate
                    ];
                })
            ];
        });

        return response()->json([
            'task' => [
                'id' => $item->id,
                'code' => $item->item_code,
                'description' => $item->description,
                'uom' => $item->uom,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'progress' => (float) $item->latest_progress
            ],
            'budget' => $budgetedCost,
            'actual' => $actualCost,
            'variance' => [
                'materials' => $budgetedCost['materials'] - $actualCost['materials'],
                'labor' => $budgetedCost['labor'] - $actualCost['labor'],
                'equipment' => $budgetedCost['equipment'] - $actualCost['equipment'],
                'total' => $budgetedCost['total'] - $actualCost['total']
            ],
            'progress_history' => $progressHistory,
            'recent_updates' => $recentUpdates
        ]);
    }
}
