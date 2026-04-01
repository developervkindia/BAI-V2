<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrDepartment extends Model
{
    use HasFactory;

    protected $table = 'hr_departments';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'parent_id',
        'head_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent()
    {
        return $this->belongsTo(HrDepartment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(HrDepartment::class, 'parent_id');
    }

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function employees()
    {
        return $this->hasMany(EmployeeProfile::class, 'hr_department_id');
    }
}
