<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrAttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'hr_attendance_logs';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_ip',
        'clock_out_ip',
        'total_hours',
        'overtime_hours',
        'status',
        'source',
        'remarks',
        'regularized_by',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'total_hours' => 'float',
        'overtime_hours' => 'float',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function regularizer()
    {
        return $this->belongsTo(User::class, 'regularized_by');
    }
}
