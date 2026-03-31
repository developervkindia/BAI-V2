<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppTaskDependency extends Model
{
    public $timestamps = false;

    protected $table = 'opp_task_dependencies';

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
        'type',
    ];

    public function task()
    {
        return $this->belongsTo(OppTask::class, 'task_id');
    }

    public function dependsOnTask()
    {
        return $this->belongsTo(OppTask::class, 'depends_on_task_id');
    }
}
