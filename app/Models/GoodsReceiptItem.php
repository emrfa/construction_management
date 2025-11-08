<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id', 'inventory_item_id', 'purchase_order_item_id',
        'quantity_received', 'unit_cost'
    ];

    public function goodsReceipt() {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function inventoryItem() {
        return $this->belongsTo(InventoryItem::class);
    }

    public function purchaseOrderItem() {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}