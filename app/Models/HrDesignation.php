<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrDesignation extends Model
{
    use HasFactory;

    protected $table = 'hr_designations';

    protected $fillable = [
        'organization_id',
        'name',
        'level',
        'hr_department_id',
        'description',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'hr_department_id');
    }
}
