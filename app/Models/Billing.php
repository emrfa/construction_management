<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'billing_no',
        'amount',
        'status',
        'billing_date',
        'notes',
    ];

    protected $casts = [
        'billing_date' => 'datetime',
    ];

    // A Billing belongs to one Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // A Billing can have one Invoice
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    // Auto-generate billing number
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($billing) {
            $year = date('Y');
            $lastBilling = DB::table('billings')
                        ->where('billing_no', 'LIKE', "BIL-{$year}-%")
                        ->orderBy('billing_no', 'desc')
                        ->first();
            $number = 1;
            if ($lastBilling) {
                $number = (int)substr($lastBilling->billing_no, -5) + 1; // Use 5 digits
            }
            $billing->billing_no = "BIL-{$year}-" . str_pad($number, 5, '0', STR_PAD_LEFT);
        });
    }
}
