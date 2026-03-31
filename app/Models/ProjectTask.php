<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'task_list_id', 'parent_task_id', 'assignee_id', 'milestone_id',
        'title', 'description', 'status', 'project_status_id', 'priority', 'position',
        'start_date', 'due_date', 'estimated_hours', 'actual_hours',
        'cost_rate_override', 'fixed_cost',
        'is_completed', 'completed_at',
        'issue_type', 'story_points',
    ];

    protected function casts(): array
    {
        return [
            'start_date'         => 'date',
            'due_date'           => 'date',
            'is_completed'       => 'boolean',
            'completed_at'       => 'datetime',
            'position'           => 'float',
            'estimated_hours'    => 'float',
            'cost_rate_override' => 'decimal:2',
            'fixed_cost'         => 'decimal:2',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function taskList()
    {
        return $this->belongsTo(TaskList::class);
    }

    public function parent()
    {
        return $this->belongsTo(ProjectTask::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(ProjectTask::class, 'parent_task_id')->orderBy('position');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function projectStatus()
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_task_members');
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class)->latest();
    }

    public function labels()
    {
        return $this->belongsToMany(ProjectLabel::class, 'project_task_labels', 'project_task_id', 'project_label_id');
    }

    public function watchers()
    {
        return $this->belongsToMany(User::class, 'project_task_watchers', 'project_task_id', 'user_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(ProjectTimeLog::class, 'project_task_id');
    }

    public function activities()
    {
        return $this->hasMany(ProjectActivity::class, 'project_task_id')->orderByDesc('created_at');
    }

    public function links()
    {
        return $this->hasMany(ProjectTaskLink::class, 'task_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function sprints()
    {
        return $this->belongsToMany(Sprint::class, 'project_sprint_tasks', 'project_task_id', 'sprint_id');
    }

    public function customFieldValues()
    {
        return $this->hasMany(ProjectTaskCustomFieldValue::class, 'project_task_id');
    }

    public function getEffectiveRateAttribute(): float
    {
        return (float) ($this->cost_rate_override ?? $this->project->hourly_rate ?? 0);
    }

    public function getActualCostAttribute(): float
    {
        $timeCost = $this->timeLogs->sum('hours') * $this->effective_rate;
        return $timeCost + (float) ($this->fixed_cost ?? 0);
    }

    public function getEstimatedCostAttribute(): float
    {
        $timeCost = (float) ($this->estimated_hours ?? 0) * $this->effective_rate;
        return $timeCost + (float) ($this->fixed_cost ?? 0);
    }

    public function getDueDateStatusAttribute(): string
    {
        if (!$this->due_date || $this->is_completed) {
            return 'none';
        }
        if ($this->due_date->isToday()) {
            return 'today';
        }
        if ($this->due_date->isPast()) {
            return 'overdue';
        }
        if ($this->due_date->diffInDays(now()) <= 2) {
            return 'soon';
        }
        return 'upcoming';
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'red',
            'high'     => 'orange',
            'medium'   => 'amber',
            'low'      => 'blue',
            default    => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->projectStatus) {
            return $this->projectStatus->color;
        }

        return match ($this->status) {
            'in_progress' => '#3B82F6',
            'completed'   => '#22C55E',
            'deferred'    => '#6B7280',
            default       => '#94A3B8',
        };
    }

    public function getStatusNameAttribute(): string
    {
        if ($this->projectStatus) {
            return $this->projectStatus->name;
        }

        return match ($this->status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'deferred'    => 'Deferred',
            default       => ucfirst($this->status),
        };
    }
}
