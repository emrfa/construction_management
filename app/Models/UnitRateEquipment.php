<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitRateEquipment extends Model
{
    use HasFactory;

    protected $table = 'unit_rate_equipments';
    
    protected $fillable = [
        'unit_rate_analysis_id',
        'equipment_id',
        'coefficient',
        'cost_rate',
    ];

    /**
     * Get the AHS header this belongs to.
     */
    public function unitRateAnalysis()
    {
        return $this->belongsTo(UnitRateAnalysis::class);
    }

    /**
     * Get the master Equipment item.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}