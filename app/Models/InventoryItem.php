<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name',
        'category',
        'uom',
        'reorder_level',
    ];

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function materialUsages()
    {
        return $this->hasMany(MaterialUsage::class);
    }

    // An item can have many transactions
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    // HELPER: Calculate current stock on hand
    public function getQuantityOnHandAttribute()
    {
        // Just sum the 'quantity' column for this item
        return $this->stockTransactions()->sum('quantity');
    }

    public function unitRateMaterials()
    {
        return $this->hasMany(UnitRateMaterial::class);
    }

    public function materialRequestItems()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }
}
