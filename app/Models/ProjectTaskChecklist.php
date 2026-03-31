<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_task_id',
        'name',
        'position',
    ];

    protected $casts = [
        'position' => 'float',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function items()
    {
        return $this->hasMany(ProjectTaskChecklistItem::class)->orderBy('position');
    }

    public function getProgressAttribute()
    {
        $total = $this->items->count();

        if ($total === 0) {
            return 0;
        }

        $checked = $this->items->where('is_checked', true)->count();

        return round(($checked / $total) * 100);
    }
}
