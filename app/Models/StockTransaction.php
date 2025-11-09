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
        'stock_location_id', 
    ];


    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }


    public function sourceable()
    {
        return $this->morphTo();
    }

    /**
     * Defines the relationship to the StockLocation model.
     */
    public function stockLocation()
    {
        return $this->belongsTo(StockLocation::class);
    }
}