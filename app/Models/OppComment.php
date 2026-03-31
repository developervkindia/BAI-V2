<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OppComment extends Model
{
    use SoftDeletes;

    protected $table = 'opp_comments';

    protected $fillable = [
        'task_id',
        'project_id',
        'parent_id',
        'user_id',
        'body',
        'body_html',
        'is_status_update',
        'edited_at',
    ];

    protected $casts = [
        'is_status_update' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(OppTask::class, 'task_id');
    }

    public function project()
    {
        return $this->belongsTo(OppProject::class, 'project_id');
    }

    public function parent()
    {
        return $this->belongsTo(OppComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(OppComment::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
