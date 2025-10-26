<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitRateMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_rate_analysis_id',
        'inventory_item_id',
        'coefficient',
        'unit_cost',
    ];

    // Belongs to one AHS Header
    public function unitRateAnalysis()
    {
        return $this->belongsTo(UnitRateAnalysis::class);
    }

    // Belongs to one Inventory Item
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
