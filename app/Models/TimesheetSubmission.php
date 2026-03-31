<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimesheetSubmission extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'week_start', 'week_end',
        'total_hours', 'status', 'reviewed_by',
        'submitted_at', 'reviewed_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'week_start'   => 'date',
            'week_end'     => 'date',
            'total_hours'  => 'float',
            'submitted_at' => 'datetime',
            'reviewed_at'  => 'datetime',
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

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
