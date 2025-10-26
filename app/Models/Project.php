<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'client_id',
        'project_code',
        'start_date',
        'end_date',
        'status',
        'total_budget',
    ];

    // A Project belongs to one Quotation
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    // A Project belongs to one Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate project_code when creating a new project
        static::creating(function ($project) {
            // Get the current year
            $year = date('Y');
            
            // Get the last project number for this year
            $lastProject = DB::table('projects')
                               ->where('project_code', 'LIKE', "P-{$year}-%")
                               ->orderBy('project_code', 'desc')
                               ->first();

            $number = 1;
            if ($lastProject) {
                // Extract the last number and increment it
                $number = (int)substr($lastProject->project_code, -4) + 1;
            }

            // Format the new project number (e.g., P-2025-0001)
            $project->project_code = "P-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    // A Project can have many Billings
    public function billings()
    {
        return $this->hasMany(Billing::class);
    }

    public function materialRequests()
    {
        return $this->hasMany(MaterialRequest::class);
    }
}
