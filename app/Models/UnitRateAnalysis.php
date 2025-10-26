<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class UnitRateAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'unit',
        'overhead_profit_percentage',
        'total_cost',
        'notes',
    ];

    
    public function materials()
    {
        return $this->hasMany(UnitRateMaterial::class);
    }

    public function labors()
    {
        return $this->hasMany(UnitRateLabor::class);
    }

    public function recalculateTotalCost()
    {
        $this->loadMissing(['materials', 'labors']);

        $baseMaterialCost = $this->materials()->sum(DB::raw('coefficient * unit_cost'));
        $baseLaborCost = $this->labors()->sum(DB::raw('coefficient * rate'));
        $baseEquipmentCost = 0; // Add later

        $baseTotalCost = $baseMaterialCost + $baseLaborCost + $baseEquipmentCost;

        // Calculate final cost including overhead/profit
        $percentage = $this->overhead_profit_percentage ?? 0;
        $finalTotalCost = $baseTotalCost * (1 + ($percentage / 100));

        $this->total_cost = $finalTotalCost;
        $this->saveQuietly();
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
    }

}
