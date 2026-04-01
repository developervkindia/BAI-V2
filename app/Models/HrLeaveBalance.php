<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrLeaveBalance extends Model
{
    use HasFactory;

    protected $table = 'hr_leave_balances';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'hr_leave_type_id',
        'year',
        'opening_balance',
        'accrued',
        'used',
        'adjusted',
        'carried_forward',
        'encashed',
        'available',
    ];

    protected $casts = [
        'opening_balance' => 'float',
        'accrued' => 'float',
        'used' => 'float',
        'adjusted' => 'float',
        'carried_forward' => 'float',
        'encashed' => 'float',
        'available' => 'float',
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
}
