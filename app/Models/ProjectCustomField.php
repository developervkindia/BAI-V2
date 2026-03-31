<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCustomField extends Model
{
    protected $fillable = [
        'project_id', 'name', 'type', 'options', 'is_required', 'position',
    ];

    protected function casts(): array
    {
        return [
            'options'     => 'array',
            'is_required' => 'boolean',
            'position'    => 'float',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function values()
    {
        return $this->hasMany(ProjectTaskCustomFieldValue::class, 'custom_field_id');
    }
}
