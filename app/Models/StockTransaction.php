<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'quantity',
        'unit_cost',
        'sourceable_id',
        'sourceable_type',
    ];

    // Get the item this transaction belongs to
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    // Get the source model (e.g., a PurchaseOrder)
    public function sourceable()
    {
        return $this->morphTo();
    }
}
