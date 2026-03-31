<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppFormSubmission extends Model
{
    public $timestamps = false;

    protected $table = 'opp_form_submissions';

    protected $fillable = [
        'form_id',
        'data',
        'task_id',
        'submitted_by_name',
        'submitted_by_email',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(OppForm::class, 'form_id');
    }

    public function task()
    {
        return $this->belongsTo(OppTask::class, 'task_id');
    }
}
