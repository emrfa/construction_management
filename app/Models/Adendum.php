<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Adendum extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AdendumItem::class);
    }

    public function calculateTotal()
    {
        $total = $this->items->sum('subtotal');
        $this->update(['total_amount' => $total]);
        return $total;
    }
}
