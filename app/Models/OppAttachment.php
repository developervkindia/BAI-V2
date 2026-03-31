<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppAttachment extends Model
{
    protected $table = 'opp_attachments';

    protected $fillable = [
        'task_id',
        'comment_id',
        'user_id',
        'filename',
        'path',
        'size',
        'mime_type',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(OppTask::class, 'task_id');
    }

    public function comment()
    {
        return $this->belongsTo(OppComment::class, 'comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
