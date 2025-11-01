<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'progress_update_id',
        'labor_rate_id',
        'quantity_used',
        'unit_cost',
    ];

    public function progressUpdate()
    {
        return $this->belongsTo(ProgressUpdate::class);
    }

    public function laborRate()
    {
        return $this->belongsTo(LaborRate::class);
    }
}