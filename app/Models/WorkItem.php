<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_type_id',
        'unit_rate_analysis_id',
        'name',
        'uom',
    ];

    /**
     * Get the work type that owns the WorkItem.
     */
    public function workType()
    {
        return $this->belongsTo(WorkType::class);
    }

    /**
     * Get the default AHS for the WorkItem.
     */
    public function unitRateAnalyses()
    {
        return $this->belongsToMany(UnitRateAnalysis::class, 'work_item_ahs'); 
    }

    /**
     * Get the group that this work item belongs to.
     */
    public function workItemGroup()
    {
        return $this->belongsTo(WorkItemGroup::class);
    }
}