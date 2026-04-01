<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrOnboarding extends Model
{
    use HasFactory;

    protected $table = 'hr_onboardings';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'status',
        'offer_letter_path',
        'expected_joining_date',
        'actual_joining_date',
        'checklist',
        'assigned_buddy_id',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'checklist' => 'array',
        'expected_joining_date' => 'date',
        'actual_joining_date' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function tasks()
    {
        return $this->hasMany(HrOnboardingTask::class);
    }

    public function buddy()
    {
        return $this->belongsTo(User::class, 'assigned_buddy_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
