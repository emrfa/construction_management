<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaterialRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_request_id',
        'inventory_item_id',
        'quotation_item_id', // Link back to WBS/RAB item
        'quantity_requested',
        'quantity_fulfilled',
    ];

    // Belongs to one Material Request header
    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    // Belongs to one Inventory Item (the material)
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    // Belongs to one Quotation Item (the WBS/RAB task, nullable)
    public function quotationItem()
    {
        return $this->belongsTo(QuotationItem::class);
    }

    
}
