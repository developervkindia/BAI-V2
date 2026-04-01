<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSalaryStructure extends Model
{
    use HasFactory;

    protected $table = 'hr_salary_structures';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'annual_ctc',
        'effective_from',
        'effective_until',
        'is_current',
    ];

    protected $casts = [
        'annual_ctc' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_current' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function components()
    {
        return $this->hasMany(HrSalaryStructureComponent::class);
    }
}
