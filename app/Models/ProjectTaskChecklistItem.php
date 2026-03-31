<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_task_checklist_id',
        'content',
        'is_checked',
        'position',
        'assigned_to',
        'due_date',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'position' => 'float',
        'due_date' => 'date',
    ];

    public function checklist()
    {
        return $this->belongsTo(ProjectTaskChecklist::class, 'project_task_checklist_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
