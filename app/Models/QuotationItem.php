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
        // Eager load children relation if not already loaded, only if needed
        if (!$this->relationLoaded('children')) {
            $this->load('children');
        }

        // --- Start Modification ---
        if ($this->children->isNotEmpty()) {
            // Calculate simple average progress for parent
            $childrenCount = $this->children->count();

            // Avoid division by zero if there are no children (shouldn't happen with isNotEmpty check, but safe)
            if ($childrenCount === 0) {
                return 0.0;
            }

            // Sum the progress percentages of all children
            $progressSum = $this->children->reduce(function ($carry, $child) {
                // Recursively get the child's progress
                // Accessing latest_progress here will trigger the same accessor on the child item.
                $childProgress = $child->latest_progress;
                return $carry + $childProgress;
            }, 0); // Start sum at 0

            // Calculate the average: (Sum of Progress / Number of Children)
            $calculatedProgress = $progressSum / $childrenCount;

            // Round to avoid excessive decimals (e.g., 1 decimal place)
            return round($calculatedProgress, 1);

        } else {
            // Original logic for leaf nodes (tasks without children)
            // Ensure progressUpdates relation is loaded if not already loaded
             if (!$this->relationLoaded('progressUpdates')) {
                $this->load('progressUpdates');
            }
            $latestUpdate = $this->progressUpdates->first(); // Gets the latest due to ordering in relationship definition
            return (float) ($latestUpdate->percent_complete ?? 0);
        }
    }

    // HELPER: Get the actual cost incurred for this item
    public function getActualCostAttribute()
    {
        // If it's a parent item, sum children's actual costs
        if ($this->children->isNotEmpty()) {
            return $this->children->sum('actual_cost');
        }

        // If it's a line item, calculate based on material usage linked to its progress updates
        $totalCost = 0;
        // Eager load material usages with their cost for efficiency
        $this->loadMissing('progressUpdates.materialUsages');

        foreach ($this->progressUpdates as $update) {
            foreach ($update->materialUsages as $usage) {
                // Ensure unit_cost is treated as a number
                $totalCost += (float)$usage->quantity_used * (float)$usage->unit_cost;
            }
        }
        return $totalCost;
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


}
