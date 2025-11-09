<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_no',
        'stock_location_id',
        'user_id',
        'adjustment_date',
        'reason',
    ];

    public function location()
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    protected static function boot() {
        parent::boot();
        static::creating(function ($adjustment) {
            $year = date('Y');
            $month = date('m');
            $prefix = "ADJ-{$year}-{$month}-";

            $lastDoc = DB::table('stock_adjustments')
                            ->where('adjustment_no', 'LIKE', "{$prefix}%")
                            ->orderBy('adjustment_no', 'desc')
                            ->first();
            $number = 1;
            if ($lastDoc) {
                $number = (int)substr($lastDoc->adjustment_no, strlen($prefix)) + 1;
            }
            $adjustment->adjustment_no = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }
}