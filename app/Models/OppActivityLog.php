<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'opp_activity_log';

    protected $fillable = [
        'task_id',
        'project_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(OppTask::class, 'task_id');
    }

    public function project()
    {
        return $this->belongsTo(OppProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
