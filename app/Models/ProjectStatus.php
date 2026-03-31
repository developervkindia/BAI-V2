<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectStatus extends Model
{
    protected $fillable = [
        'project_id', 'name', 'slug', 'color', 'position',
        'is_completed_state', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_completed_state' => 'boolean',
            'is_default'         => 'boolean',
            'position'           => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProjectStatus $status) {
            if (empty($status->slug)) {
                $status->slug = Str::slug($status->name, '_');
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_status_id');
    }

    public static function seedDefaults(Project $project): void
    {
        $defaults = [
            ['name' => 'Open',        'slug' => 'open',        'color' => '#94A3B8', 'position' => 1, 'is_completed_state' => false, 'is_default' => true],
            ['name' => 'In Progress', 'slug' => 'in_progress', 'color' => '#3B82F6', 'position' => 2, 'is_completed_state' => false, 'is_default' => false],
            ['name' => 'Completed',   'slug' => 'completed',   'color' => '#22C55E', 'position' => 3, 'is_completed_state' => true,  'is_default' => false],
            ['name' => 'Deferred',    'slug' => 'deferred',    'color' => '#6B7280', 'position' => 4, 'is_completed_state' => false, 'is_default' => false],
        ];

        foreach ($defaults as $s) {
            $project->statuses()->create($s);
        }
    }
}
