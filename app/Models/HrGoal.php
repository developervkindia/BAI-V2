<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrGoal extends Model
{
    use HasFactory;

    protected $table = 'hr_goals';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'hr_review_cycle_id',
        'parent_id',
        'title',
        'description',
        'goal_type',
        'metric_type',
        'target_value',
        'current_value',
        'weightage',
        'status',
        'start_date',
        'due_date',
    ];

    protected $casts = [
        'target_value' => 'float',
        'current_value' => 'float',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function reviewCycle()
    {
        return $this->belongsTo(HrReviewCycle::class, 'hr_review_cycle_id');
    }

    public function parent()
    {
        return $this->belongsTo(HrGoal::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(HrGoal::class, 'parent_id');
    }

    public function getProgressAttribute()
    {
        if ($this->target_value > 0) {
            return ($this->current_value / $this->target_value) * 100;
        }

        return 0;
    }
}
