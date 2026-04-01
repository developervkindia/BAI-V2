<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrCompOff extends Model
{
    use HasFactory;

    protected $table = 'hr_comp_offs';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'worked_on',
        'expires_on',
        'days',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'worked_on' => 'date',
        'expires_on' => 'date',
        'days' => 'float',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
