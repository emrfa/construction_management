<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TransferShipment;

class TransferReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'transfer_shipment_id',
        'received_date',
        'received_by_user_id',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    public function shipment()
    {
        return $this->belongsTo(TransferShipment::class, 'transfer_shipment_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
