<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaborRate extends Model
{
    use HasFactory;

        protected $fillable = [
            'labor_type',
            'unit',
            'rate',
        ];

        public function unitRateLabors()
    {
        return $this->hasMany(UnitRateLabor::class);
    }
}
