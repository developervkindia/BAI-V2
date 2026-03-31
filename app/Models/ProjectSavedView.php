<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSavedView extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'name', 'filters',
        'sort_by', 'sort_direction', 'group_by',
        'view_type', 'is_shared', 'position',
    ];

    protected function casts(): array
    {
        return [
            'filters'   => 'array',
            'is_shared' => 'boolean',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
