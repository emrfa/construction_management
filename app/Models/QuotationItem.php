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
        //return $this->progressUpdates()->latest('date')->first()->percent_complete ?? 0;
        $latestUpdate = $this->progressUpdates->first();

        // Use null coalescing to safely return percent_complete or 0
        return (float) ($latestUpdate->percent_complete ?? 0);
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
