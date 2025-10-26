<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'inventory_item_id',
        'material_request_item_id',
        'quantity_ordered',
        'quantity_received',
        'unit_cost',
        'subtotal',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
