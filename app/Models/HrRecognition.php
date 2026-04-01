<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrRecognition extends Model
{
    use HasFactory;

    protected $table = 'hr_recognitions';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'recognized_by',
        'type',
        'title',
        'description',
        'badge_icon',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function recognizer()
    {
        return $this->belongsTo(User::class, 'recognized_by');
    }
}
