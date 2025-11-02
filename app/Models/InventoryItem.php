<?php

namespace App\Models;
use App\Models\ItemCategory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name',
        'category_id',
        'uom',
        'base_purchase_price', 
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

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Only generate if item_code is not already set
            if (is_null($item->item_code)) {
                $category = \App\Models\ItemCategory::find($item->category_id);
                $prefix = $category ? $category->prefix : 'GEN';


                // 1. Find all item codes that start with this prefix.
                $allCodes = DB::table('inventory_items')
                    ->where('item_code', 'LIKE', $prefix . '-%')
                    ->pluck('item_code');

                $maxNumber = 0;
                
                // 2. Loop through them and find the highest number
                foreach ($allCodes as $code) {
                    // Find the part *after* the last hyphen
                    $numberPart = substr($code, strrpos($code, '-') + 1);

                    // Check if it's a valid number
                    if (is_numeric($numberPart)) {
                        $number = (int)$numberPart;
                        if ($number > $maxNumber) {
                            $maxNumber = $number;
                        }
                    }
                }

                // 3. The new number is the highest one + 1
                $newNumber = $maxNumber + 1;

                // 4. Pad it to 3 digits (e.g., 2 becomes "002")
                $item->item_code = $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
                
            }
        });
    }
}
