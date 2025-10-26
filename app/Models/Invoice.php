<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'client_id',
        'invoice_no',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'issued_date',
        'due_date',
    ];

    // An Invoice belongs to one Billing record
    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    // An Invoice belongs to one Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // An Invoice can have many Payments
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Helper to get total paid amount
    public function getTotalPaidAttribute()
    {
        // Use ->get()->sum() if eager loading, or ->sum() directly if not
        return $this->payments()->sum('amount');
    }

    // Helper to get remaining balance
    public function getRemainingBalanceAttribute()
    {
        // Ensure total_amount is treated as a number
        return (float) $this->total_amount - (float) $this->getTotalPaidAttribute();
    }

    // Auto-generate invoice number
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($invoice) {
            $year = date('Y');
            $lastInvoice = DB::table('invoices')
                        ->where('invoice_no', 'LIKE', "INV-{$year}-%")
                        ->orderBy('invoice_no', 'desc')
                        ->first();
            $number = 1;
            if ($lastInvoice) {
                $number = (int)substr($lastInvoice->invoice_no, -5) + 1; // Use 5 digits
            }
            $invoice->invoice_no = "INV-{$year}-" . str_pad($number, 5, '0', STR_PAD_LEFT);
        });
    }
}
