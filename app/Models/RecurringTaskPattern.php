<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTaskPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_list_id',
        'title',
        'description',
        'assignee_id',
        'priority',
        'issue_type',
        'frequency',
        'day_of_week',
        'day_of_month',
        'next_run_date',
        'last_run_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'next_run_date' => 'date',
        'last_run_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function taskList()
    {
        return $this->belongsTo(TaskList::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
