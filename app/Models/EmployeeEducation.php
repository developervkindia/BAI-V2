<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEducation extends Model
{
    use HasFactory;

    protected $table = 'employee_education';

    protected $fillable = [
        'employee_profile_id',
        'degree',
        'institution',
        'field_of_study',
        'start_year',
        'end_year',
        'grade',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
    }
}
