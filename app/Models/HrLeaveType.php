<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrLeaveType extends Model
{
    use HasFactory;

    protected $table = 'hr_leave_types';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'color',
        'is_paid',
        'max_days_per_year',
        'accrual_type',
        'accrual_count',
        'carry_forward_limit',
        'encashable',
        'requires_approval',
        'min_days',
        'max_consecutive_days',
        'sandwich_policy',
        'applicable_gender',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'max_days_per_year' => 'float',
        'accrual_count' => 'float',
        'encashable' => 'boolean',
        'requires_approval' => 'boolean',
        'sandwich_policy' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function balances()
    {
        return $this->hasMany(HrLeaveBalance::class);
    }

    public function requests()
    {
        return $this->hasMany(HrLeaveRequest::class);
    }
}
