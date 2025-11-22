<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalTransferItem extends Model
{
    protected $fillable = [
        'internal_transfer_id',
        'inventory_item_id',
        'quantity_requested',
        'quantity_shipped',
    ];

    public function transfer()
    {
        return $this->belongsTo(InternalTransfer::class, 'internal_transfer_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
