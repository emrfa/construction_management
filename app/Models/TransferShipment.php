<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InternalTransfer;
use App\Models\StockLocation;
use App\Models\TransferShipmentItem;
use App\Models\TransferReceipt;
use App\Models\User;

class TransferShipment extends Model
{
    protected $fillable = [
        'shipment_number',
        'internal_transfer_id',
        'source_location_id',
        'destination_location_id',
        'shipped_date',
        'status',
        'shipped_by_user_id',
        'notes',
    ];

    protected $casts = [
        'shipped_date' => 'date',
    ];

    public function internalTransfer()
    {
        return $this->belongsTo(InternalTransfer::class);
    }

    public function sourceLocation()
    {
        return $this->belongsTo(StockLocation::class, 'source_location_id');
    }

    public function destinationLocation()
    {
        return $this->belongsTo(StockLocation::class, 'destination_location_id');
    }

    public function items()
    {
        return $this->hasMany(TransferShipmentItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(TransferReceipt::class);
    }

    public function shippedBy()
    {
        return $this->belongsTo(User::class, 'shipped_by_user_id');
    }
}
