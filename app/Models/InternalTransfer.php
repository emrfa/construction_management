<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockLocation;
use App\Models\InternalTransferItem;
use App\Models\TransferShipment;
use App\Models\User;
use App\Models\MaterialRequest;

class InternalTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'source_location_id',
        'destination_location_id',
        'status',
        'created_by_user_id',
        'material_request_id',
        'notes',
    ];

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
        return $this->hasMany(InternalTransferItem::class);
    }

    public function shipments()
    {
        return $this->hasMany(TransferShipment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class);
    }
}
