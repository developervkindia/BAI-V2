<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTaskLink extends Model
{
    protected $fillable = ['task_id', 'linked_task_id', 'type'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function linkedTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'linked_task_id');
    }
}
