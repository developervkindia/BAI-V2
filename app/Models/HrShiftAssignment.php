<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrShiftAssignment extends Model
{
    use HasFactory;

    protected $table = 'hr_shift_assignments';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'hr_shift_id',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function shift()
    {
        return $this->belongsTo(HrShift::class, 'hr_shift_id');
    }
}
