<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeLog extends Model
{
    protected $fillable = [
        'project_task_id',
        'user_id',
        'hours',
        'notes',
        'logged_at',
        'is_billable',
        'timer_started_at',
        'timer_stopped_at',
        'is_timer_entry',
    ];

    protected $casts = [
        'logged_at'        => 'date',
        'hours'            => 'float',
        'is_billable'      => 'boolean',
        'timer_started_at' => 'datetime',
        'timer_stopped_at' => 'datetime',
        'is_timer_entry'   => 'boolean',
    ];

    public function isRunning(): bool
    {
        return $this->timer_started_at && !$this->timer_stopped_at;
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
