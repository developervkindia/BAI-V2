<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'employee_id',
        'designation',
        'department',
        'date_of_joining',
        'employment_type',
        'reporting_manager_id',
        'work_location',
        'shift',
        'phone',
        'personal_email',
        'work_phone',
        'date_of_birth',
        'gender',
        'marital_status',
        'blood_group',
        'nationality',
        'emergency_contact_name',
        'emergency_contact_phone',
        'current_address',
        'permanent_address',
        'bank_name',
        'account_number',
        'ifsc_code',
        'bank_branch',
        'status',
        'deactivated_at',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
        'date_of_birth' => 'date',
        'deactivated_at' => 'datetime',
        'account_number' => 'encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

    public function education(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function experience(): HasMany
    {
        return $this->hasMany(EmployeeExperience::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(EmployeeAsset::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function currentSalaryStructure()
    {
        return $this->hasOne(HrSalaryStructure::class)->where('is_current', true);
    }
}
