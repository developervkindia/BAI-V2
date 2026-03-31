<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use SoftDeletes;
    protected $fillable = ['project_id', 'name', 'description', 'due_date', 'status'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function taskLists()
    {
        return $this->hasMany(TaskList::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $this->tasks()->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }
}
