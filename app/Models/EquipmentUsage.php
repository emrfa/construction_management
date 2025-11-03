<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'progress_update_id',
        'equipment_id',
        'quantity_used',
        'unit_used',
        'total_cost',
    ];

    /**
     * Get the progress log this belongs to.
     */
    public function progressUpdate()
    {
        return $this->belongsTo(ProgressUpdate::class);
    }

    /**
     * Get the master equipment item.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}