<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectScopeChange extends Model
{
    protected $fillable = [
        'project_id', 'requested_by', 'title', 'description',
        'type', 'cost_impact', 'days_impact', 'status',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'cost_impact'  => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
