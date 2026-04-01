<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrPayrollEntry extends Model
{
    use HasFactory;

    protected $table = 'hr_payroll_entries';

    protected $fillable = [
        'hr_payroll_run_id',
        'employee_profile_id',
        'gross_earnings',
        'total_deductions',
        'net_pay',
        'working_days',
        'days_present',
        'lop_days',
        'components',
        'status',
    ];

    protected $casts = [
        'gross_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'lop_days' => 'float',
        'components' => 'array',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(HrPayrollRun::class, 'hr_payroll_run_id');
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
