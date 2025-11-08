<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'material_request_id',
        'po_number',
        'project_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // Add the polymorphic relationship for Stock Transactions
    public function stockTransactions()
    {
        return $this->morphMany(StockTransaction::class, 'sourceable');
    }

    // Auto-generate PO number
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($po) {
            $year = date('Y');
            $lastPo = DB::table('purchase_orders')
                        ->where('po_number', 'LIKE', "PO-{$year}-%")
                        ->orderBy('po_number', 'desc')
                        ->first();
            $number = 1;
            if ($lastPo) {
                $number = (int)substr($lastPo->po_number, -4) + 1;
            }
            $po->po_number = "PO-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    public function goodsReceipts()
    {
        return $this->hasMany(\App\Models\GoodsReceipt::class);
    }

    /**
     * Get the material request that this purchase order was created for.
     */
    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class);
    }
}
