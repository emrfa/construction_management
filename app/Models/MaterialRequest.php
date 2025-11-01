<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MaterialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'project_id',
        'requested_by_user_id',
        'approved_by_user_id',
        'request_date',
        'required_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'required_date' => 'datetime',
    ];

    // Relationship to the Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relationship to the User who requested
    public function requester()
    {
        // Explicitly define foreign key if it differs from default user_id
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    // Relationship to the User who approved (nullable)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    // Relationship to the line items on this request
    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }

    // Auto-generate request_code
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($request) {
            $year = date('Y');
            $month = date('m');
            // Example format: MR-YYYY-MM-0001
            $prefix = "MR-{$year}-{$month}-";
            $lastRequest = DB::table('material_requests')
                            ->where('request_code', 'LIKE', "{$prefix}%")
                            ->orderBy('request_code', 'desc')
                            ->first();
            $number = 1;
            if ($lastRequest) {
                // Extract number after the prefix
                $number = (int)substr($lastRequest->request_code, strlen($prefix)) + 1;
            }
            $request->request_code = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }
}
