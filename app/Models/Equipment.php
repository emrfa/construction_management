<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment'; // Explicitly define table name

    protected $fillable = [
       'name',
        'identifier', // Asset Code
        'type',
        'status',
        'supplier_id',
        'rental_start_date',
        'rental_end_date',
        'rental_rate',
        'rental_rate_unit',
        'rental_agreement_ref',
        'base_purchase_price', 
        'base_rental_rate',   
        'base_rental_rate_unit', 
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get all the AHS line items this equipment is used in.
     */
    public function unitRateEquipments()
    {
        return $this->hasMany(UnitRateEquipment::class);
    }
}
