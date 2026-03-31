<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskList extends Model
{
    use SoftDeletes;
    protected $fillable = ['project_id', 'milestone_id', 'name', 'position', 'color'];

    protected function casts(): array
    {
        return [
            'position' => 'float',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class)->orderBy('position');
    }

    public function activeTasks()
    {
        return $this->tasks()->whereNull('parent_task_id')->whereNull('deleted_at');
    }
}
