<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrOnboardingTask extends Model
{
    use HasFactory;

    protected $table = 'hr_onboarding_tasks';

    protected $fillable = [
        'hr_onboarding_id',
        'title',
        'description',
        'assigned_to',
        'is_completed',
        'completed_at',
        'due_date',
        'sort_order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function onboarding()
    {
        return $this->belongsTo(HrOnboarding::class, 'hr_onboarding_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
