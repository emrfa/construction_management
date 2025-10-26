<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitRateLabor extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_rate_analysis_id',
        'labor_rate_id',
        'coefficient',
        'rate',
    ];

    // Belongs to one AHS Header
    public function unitRateAnalysis()
    {
        return $this->belongsTo(UnitRateAnalysis::class);
    }

    // Belongs to one Labor Rate entry
    public function laborRate()
    {
        return $this->belongsTo(LaborRate::class);
    }
}
