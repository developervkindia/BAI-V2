<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'type',
        'name',
        'asset_tag',
        'serial_number',
        'assigned_date',
        'return_date',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'return_date' => 'date',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
    }
}
