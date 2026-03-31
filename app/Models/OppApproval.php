<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppApproval extends Model
{
    protected $table = 'opp_approvals';

    protected $fillable = [
        'task_id',
        'status',
        'requested_by',
        'decided_by',
        'decided_at',
        'comment',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(OppTask::class, 'task_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
