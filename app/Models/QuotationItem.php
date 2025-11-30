<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'parent_id',
        'unit_rate_analysis_id',
        'description',
        'item_code',
        'uom',
        'quantity',
        'unit_price',
        'subtotal',
        'sort_order',
    ];

    protected $casts = [
        'planned_start' => 'datetime',
        'planned_end' => 'datetime',
    ];

    // ... quotation() relationship is here ...
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    // vvv ADD THESE RELATIONSHIPS vvv

    /**
     * Get the parent item.
     */
    public function parent()
    {
        return $this->belongsTo(QuotationItem::class, 'parent_id');
    }

    /**
     * Get the child items.
     */
    public function children()
    {
        return $this->hasMany(QuotationItem::class, 'parent_id')->orderBy('sort_order');
    }

    // A task can have many progress updates
    public function progressUpdates()
    {
        return $this->hasMany(ProgressUpdate::class)->orderBy('date', 'desc')
        ->orderBy('id', 'desc');
    }

    // HELPER: Get the *latest* progress percentage
   public function getLatestProgressAttribute()
    {
        // If it's a parent, progress is the average of its children's progress
        if ($this->children->isNotEmpty()) {
            if (!$this->relationLoaded('children')) {
                $this->load('children');
            }
            if ($this->children->count() == 0) return 0;

            $progressSum = $this->children->reduce(function ($carry, $child) {
                return $carry + $child->latest_progress;
            }, 0);
            
            return round($progressSum / $this->children->count(), 2);
        }

        // --- If it's a Line Item (AHS Task) ---
        // Get the most recent progress update for this item
        $this->loadMissing('progressUpdates'); // Make sure updates are loaded

        $latestUpdate = $this->progressUpdates->first(); // Gets the latest due to ordering in relationship
        
        return $latestUpdate ? (float) $latestUpdate->percent_complete : 0;
    }

    // HELPER: Get the actual cost incurred for this item
    public function getActualCostAttribute()
    {
        if ($this->children->isNotEmpty()) {
            if (!$this->relationLoaded('children')) {
                // PERFORMANCE FIX: This stops N+1 queries on child items
                $this->loadMissing([
                    'children.progressUpdates.materialUsages',
                    'children.progressUpdates.laborUsages',
                    'children.progressUpdates.equipmentUsages'
                ]);
            }
            return $this->children->sum('actual_cost');
        }

        // If it's a line item, calculate based on material and labor usage
        // We must Eager Load the relationships
        $this->loadMissing('progressUpdates.materialUsages', 'progressUpdates.laborUsages', 'progressUpdates.equipmentUsages');

        $materialCost = 0;
        $laborCost = 0;
        $equipmentCost = 0;

        foreach ($this->progressUpdates as $update) {
            $materialCost += $update->materialUsages->sum(function($usage) {
                return $usage->quantity_used * $usage->unit_cost;
            });

            $laborCost += $update->laborUsages->sum(function($usage) {
                return $usage->quantity_used * $usage->unit_cost;
            });
            $equipmentCost += $update->equipmentUsages->sum('total_cost');
        }

        return $materialCost + $laborCost + $equipmentCost;
    }

    // HELPER: Get remaining budget for this item
    public function getBudgetLeftAttribute()
    {
        // Ensure subtotal (budget) and actual_cost are treated as numbers
        return (float)$this->subtotal - (float)$this->getActualCostAttribute();
    }


    public function unitRateAnalysis()
    {
        return $this->belongsTo(UnitRateAnalysis::class);
    }

    public function materialRequestItems()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }

    public function adendum()
    {
        return $this->belongsTo(Adendum::class);
    }
}
