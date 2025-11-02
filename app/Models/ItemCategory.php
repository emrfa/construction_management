<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ItemCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix',
    ];

    /**
     * The "booted" method of the model.
     * This will automatically generate the prefix.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            // Only generate a prefix if one wasn't manually provided
            if (empty($category->prefix)) {
                // 1. Create the base prefix (e.g., "Steel" -> "STE")
                $basePrefix = strtoupper(substr($category->name, 0, 3));
                $prefix = $basePrefix;
                
                // 2. Handle collisions (like "Steel" and "Steve")
                $counter = 1;
                // Keep checking the database until we find a unique prefix
                while (static::where('prefix', $prefix)->exists()) {
                    $prefix = $basePrefix . $counter;
                    $counter++;
                }

                // 3. Assign the unique prefix
                $category->prefix = $prefix;
            }
        });
    }

    /**
     * An Item Category has many Inventory Items.
     */
    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }
}