<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppTaskFollower extends Model
{
    public $timestamps = false;

    protected $table = 'opp_task_followers';

    protected $fillable = [
        'task_id',
        'user_id',
    ];

    public function task()
    {
        return $this->belongsTo(OppTask::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
