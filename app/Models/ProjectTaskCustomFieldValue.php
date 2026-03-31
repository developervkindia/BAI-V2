<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskCustomFieldValue extends Model
{
    protected $fillable = [
        'project_task_id', 'custom_field_id', 'value',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function field()
    {
        return $this->belongsTo(ProjectCustomField::class, 'custom_field_id');
    }
}
