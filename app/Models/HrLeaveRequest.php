<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrLeaveRequest extends Model
{
    use HasFactory;

    protected $table = 'hr_leave_requests';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'hr_leave_type_id',
        'start_date',
        'end_date',
        'days',
        'is_half_day',
        'half_day_period',
        'reason',
        'status',
        'approved_by',
        'rejected_by',
        'rejection_reason',
        'applied_at',
        'actioned_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'float',
        'is_half_day' => 'boolean',
        'applied_at' => 'datetime',
        'actioned_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(HrLeaveType::class, 'hr_leave_type_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
