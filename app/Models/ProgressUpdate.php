<?php

namespace App\Models;

use App\Models\InventoryItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_item_id',
        'user_id',
        'date',
        'percent_complete',
        'notes',
    ];

    // An update belongs to one task
    public function quotationItem()
    {
        return $this->belongsTo(QuotationItem::class);
    }

    // An update belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function materialUsages()
    {
        return $this->hasMany(MaterialUsage::class);
    }

}