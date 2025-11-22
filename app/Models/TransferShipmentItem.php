<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransferShipment;
use App\Models\InventoryItem;

class TransferShipmentItem extends Model
{
    protected $fillable = [
        'transfer_shipment_id',
        'inventory_item_id',
        'quantity_shipped',
        'quantity_received',
        'unit_cost',
    ];

    public function shipment()
    {
        return $this->belongsTo(TransferShipment::class, 'transfer_shipment_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
