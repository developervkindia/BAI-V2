<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrPayrollRun extends Model
{
    use HasFactory;

    protected $table = 'hr_payroll_runs';

    protected $fillable = [
        'organization_id',
        'month',
        'year',
        'status',
        'total_gross',
        'total_deductions',
        'total_net',
        'employee_count',
        'processed_by',
        'processed_at',
        'finalized_at',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'processed_at' => 'datetime',
        'finalized_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function entries()
    {
        return $this->hasMany(HrPayrollEntry::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
