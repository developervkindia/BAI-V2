<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrExit extends Model
{
    use HasFactory;

    protected $table = 'hr_exits';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'type',
        'reason',
        'resignation_date',
        'last_working_date',
        'status',
        'fnf_amount',
        'fnf_settled_at',
        'exit_interview_notes',
        'created_by',
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'last_working_date' => 'date',
        'fnf_amount' => 'decimal:2',
        'fnf_settled_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function clearances()
    {
        return $this->hasMany(HrExitClearance::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
