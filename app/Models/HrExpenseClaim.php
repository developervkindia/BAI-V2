<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrExpenseClaim extends Model
{
    use HasFactory;

    protected $table = 'hr_expense_claims';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'title',
        'total_amount',
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'reimbursed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'reimbursed_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function items()
    {
        return $this->hasMany(HrExpenseItem::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
