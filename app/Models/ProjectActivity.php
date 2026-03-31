<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectActivity extends Model
{
    const UPDATED_AT = null;

    protected $table = 'project_task_activities';

    protected $fillable = [
        'project_task_id',
        'user_id',
        'type',
        'field_name',
        'old_value',
        'new_value',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDescriptionAttribute(): string
    {
        $user = $this->user->name ?? 'Someone';

        return match ($this->field_name) {
            'status'       => "{$user} changed status from <strong>" . ($this->old_value ?? 'none') . "</strong> to <strong>{$this->new_value}</strong>",
            'priority'     => "{$user} changed priority from <strong>" . ($this->old_value ?? 'none') . "</strong> to <strong>{$this->new_value}</strong>",
            'assignee_id'  => $this->new_value
                                ? "{$user} assigned this task"
                                : "{$user} unassigned this task",
            'milestone_id' => $this->new_value
                                ? "{$user} set a milestone"
                                : "{$user} removed the milestone",
            'due_date'     => $this->new_value
                                ? "{$user} set due date to <strong>{$this->new_value}</strong>"
                                : "{$user} removed the due date",
            'title'        => "{$user} renamed this task",
            'is_completed' => $this->new_value
                                ? "{$user} marked this task <strong>completed</strong>"
                                : "{$user} reopened this task",
            'issue_type'   => "{$user} changed type to <strong>{$this->new_value}</strong>",
            default        => "{$user} updated this task",
        };
    }
}
