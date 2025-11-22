<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'stock_location_id',
        'quantity',
        'average_unit_cost',
        'last_transaction_id',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function stockLocation()
    {
        return $this->belongsTo(StockLocation::class);
    }
}
