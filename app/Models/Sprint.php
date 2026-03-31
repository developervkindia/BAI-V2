<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sprint extends Model
{
    protected $table = 'project_sprints';

    protected $fillable = ['project_id', 'name', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(
            ProjectTask::class,
            'project_sprint_tasks',
            'sprint_id',
            'project_task_id'
        );
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks->count();
        if ($total === 0) return 0;
        return (int) round(($this->tasks->where('is_completed', true)->count() / $total) * 100);
    }
}
