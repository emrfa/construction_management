<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Illuminate\Database\Eloquent\Model;
use App\Models\WorkItem;

class WorkType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get all of the work items for the WorkType.
     */
    public function workItems()
    {
        return $this->belongsToMany(WorkItem::class, 'work_type_work_item');
    }
}
