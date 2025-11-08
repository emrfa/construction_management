<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no', 'supplier_id', 'purchase_order_id', 'project_id',
        'received_by_user_id', 'receipt_date', 'status', 'notes'
    ];

    protected $casts = [
        'receipt_date' => 'datetime',
    ];

    public function items() {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function receiver() {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    // This is the new source for stock transactions
    public function stockTransactions() {
        return $this->morphMany(StockTransaction::class, 'sourceable');
    }

    protected static function boot() {
        parent::boot();
        static::creating(function ($receipt) {
            $year = date('Y');
            $month = date('m');
            $prefix = "GRN-{$year}-{$month}-";

            $lastReceipt = DB::table('goods_receipts')
                            ->where('receipt_no', 'LIKE', "{$prefix}%")
                            ->orderBy('receipt_no', 'desc')
                            ->first();
            $number = 1;
            if ($lastReceipt) {
                $number = (int)substr($lastReceipt->receipt_no, strlen($prefix)) + 1;
            }
            $receipt->receipt_no = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    public function backOrderReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'back_order_receipt_id');
    }
}