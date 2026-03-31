<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OppTask extends Model
{
    use SoftDeletes;

    protected $table = 'opp_tasks';

    protected $fillable = [
        'project_id',
        'section_id',
        'parent_task_id',
        'title',
        'description',
        'description_html',
        'assignee_id',
        'status',
        'completed_at',
        'completed_by',
        'due_date',
        'due_time',
        'start_date',
        'is_milestone',
        'position',
        'likes_count',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'date',
        'completed_at' => 'datetime',
        'is_milestone' => 'boolean',
        'position' => 'float',
        'likes_count' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(OppProject::class, 'project_id');
    }

    public function section()
    {
        return $this->belongsTo(OppSection::class, 'section_id');
    }

    public function parent()
    {
        return $this->belongsTo(OppTask::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(OppTask::class, 'parent_task_id')->orderBy('position');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'opp_task_assignees', 'task_id', 'user_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'opp_task_followers', 'task_id', 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(OppTag::class, 'opp_task_tags', 'task_id', 'tag_id');
    }

    public function comments()
    {
        return $this->hasMany(OppComment::class, 'task_id')->latest();
    }

    public function attachments()
    {
        return $this->hasMany(OppAttachment::class, 'task_id');
    }

    public function likes()
    {
        return $this->hasMany(OppTaskLike::class, 'task_id');
    }

    public function dependencies()
    {
        return $this->hasMany(OppTaskDependency::class, 'task_id');
    }

    public function dependedOnBy()
    {
        return $this->hasMany(OppTaskDependency::class, 'depends_on_task_id');
    }

    public function customFieldValues()
    {
        return $this->hasMany(OppTaskCustomFieldValue::class, 'task_id');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }
}
