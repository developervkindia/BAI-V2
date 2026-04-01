<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrShift extends Model
{
    use HasFactory;

    protected $table = 'hr_shifts';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'grace_minutes',
        'is_night_shift',
        'break_duration_minutes',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_night_shift' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignments()
    {
        return $this->hasMany(HrShiftAssignment::class);
    }
}
