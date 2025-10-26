<?php

namespace App\Observers;

use App\Models\UnitRateAnalysis;

class UnitRateAnalysisObserver
{
    /**
     * Handle the UnitRateAnalysis "created" event.
     */
    public function created(UnitRateAnalysis $unitRateAnalysis): void
    {
        //
    }

    /**
     * Handle the UnitRateAnalysis "updated" event.
     */
    public function updated(UnitRateAnalysis $unitRateAnalysis): void
    {
        //
    }

    /**
     * Handle the UnitRateAnalysis "deleted" event.
     */
    public function deleted(UnitRateAnalysis $unitRateAnalysis): void
    {
        //
    }

    /**
     * Handle the UnitRateAnalysis "restored" event.
     */
    public function restored(UnitRateAnalysis $unitRateAnalysis): void
    {
        //
    }

    /**
     * Handle the UnitRateAnalysis "force deleted" event.
     */
    public function forceDeleted(UnitRateAnalysis $unitRateAnalysis): void
    {
        //
    }

    /**
     * Handle the UnitRateAnalysis "saving" event.
     * Recalculates total cost based on materials and labor BEFORE saving.
     *
     * @param  \App\Models\UnitRateAnalysis  $unitRateAnalysis
     * @return void
     */
    public function saving(UnitRateAnalysis $unitRateAnalysis): void
    {
        // Check if the model exists and relations are loaded or accessible
        // Note: This calculation might be better triggered *after* materials/labors are saved.
        // For simplicity now, let's assume it recalculates based on current relations.
        // A more robust solution might involve triggering this calculation from
        // the material/labor saving events.

        $materialCost = $unitRateAnalysis->materials()
                            ->sum(DB::raw('coefficient * unit_cost')); // Assuming unit_cost is stored

        $laborCost = $unitRateAnalysis->labors()
                         ->sum(DB::raw('coefficient * rate')); // Assuming rate is stored

        // Add equipment cost here later if needed
        $equipmentCost = 0;

        $unitRateAnalysis->total_cost = $materialCost + $laborCost + $equipmentCost;
    }
}
