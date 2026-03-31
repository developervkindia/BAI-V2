<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectWeeklyUpdate extends Model
{
    protected $fillable = [
        'project_id', 'created_by', 'title', 'period_type',
        'week_start', 'week_end', 'summary', 'next_steps', 'blockers',
        'qa_approved_by', 'qa_approved_at', 'shared_with_client_at',
    ];

    protected $casts = [
        'week_start'             => 'date',
        'week_end'               => 'date',
        'qa_approved_at'         => 'datetime',
        'shared_with_client_at'  => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function qaApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qa_approved_by');
    }
}
