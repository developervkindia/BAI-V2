<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBillingEntry extends Model
{
    protected $fillable = [
        'billing_week_id', 'user_id', 'actual_hours', 'billable_hours', 'notes',
    ];

    protected $casts = [
        'actual_hours'   => 'decimal:2',
        'billable_hours' => 'decimal:2',
    ];

    public function billingWeek(): BelongsTo
    {
        return $this->belongsTo(ProjectBillingWeek::class, 'billing_week_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
