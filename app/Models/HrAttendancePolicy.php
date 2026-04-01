<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrAttendancePolicy extends Model
{
    use HasFactory;

    protected $table = 'hr_attendance_policies';

    protected $fillable = [
        'organization_id',
        'name',
        'late_mark_after_minutes',
        'half_day_after_minutes',
        'absent_after_minutes',
        'overtime_threshold_minutes',
        'overtime_rate',
        'is_default',
    ];

    protected $casts = [
        'overtime_rate' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
