<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBillingWeek extends Model
{
    protected $fillable = [
        'project_id', 'week_start', 'week_end',
        'total_actual_hours', 'total_billable_hours', 'total_amount',
        'locked_by', 'locked_at', 'invoice_sent_at',
    ];

    protected $casts = [
        'week_start'       => 'date',
        'week_end'         => 'date',
        'locked_at'        => 'datetime',
        'invoice_sent_at'  => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ProjectBillingEntry::class, 'billing_week_id');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function recalculateTotals(): void
    {
        $this->total_actual_hours   = $this->entries->sum('actual_hours');
        $this->total_billable_hours = $this->entries->sum('billable_hours');
        $this->total_amount         = $this->total_billable_hours * ($this->project->hourly_rate ?? 0);
        $this->save();
    }
}
