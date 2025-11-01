<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'quotation_no',
        'project_name',
        'date',
        'status',
        'total_estimate',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get all items (for calculation, etc.).
     */
    public function allItems()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function project()
{
    return $this->hasOne(Project::class);
}

    /**
     * Get only the ROOT items (where parent_id is null).
     */
    // vvv MODIFY THIS RELATIONSHIP vvv
    public function items()
    {
        return $this->hasMany(QuotationItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }
    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate quotation_no when creating a new quotation
        static::creating(function ($quotation) {
            // Get the current year
            $year = date('Y');

            // Get the last quotation number for this year
            $lastQuotation = DB::table('quotations')
                               ->where('quotation_no', 'LIKE', "Q-{$year}-%")
                               ->orderBy('quotation_no', 'desc')
                               ->first();

            $number = 1;
            if ($lastQuotation) {
                // Extract the last number and increment it
                $number = (int)substr($lastQuotation->quotation_no, -4) + 1;
            }

            // Format the new quotation number (e.g., Q-2025-0001)
            $quotation->quotation_no = "Q-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }
}
