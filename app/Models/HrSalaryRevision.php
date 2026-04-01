<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSalaryRevision extends Model
{
    use HasFactory;

    protected $table = 'hr_salary_revisions';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'previous_ctc',
        'new_ctc',
        'effective_from',
        'reason',
        'approved_by',
    ];

    protected $casts = [
        'previous_ctc' => 'decimal:2',
        'new_ctc' => 'decimal:2',
        'effective_from' => 'date',
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
