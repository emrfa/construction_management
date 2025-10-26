<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaterialUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'progress_update_id',
        'inventory_item_id',
        'quantity_used',
        'unit_cost', // Add this if you want to store cost at time of usage
    ];

    public function progressUpdate()
    {
        return $this->belongsTo(ProgressUpdate::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
