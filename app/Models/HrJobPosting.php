<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrJobPosting extends Model
{
    use HasFactory;

    protected $table = 'hr_job_postings';

    protected $fillable = [
        'organization_id',
        'title',
        'hr_department_id',
        'hr_designation_id',
        'description',
        'requirements',
        'employment_type',
        'location',
        'salary_range_min',
        'salary_range_max',
        'positions',
        'status',
        'posted_by',
        'posted_at',
        'closed_at',
    ];

    protected $casts = [
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
        'posted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'hr_department_id');
    }

    public function designation()
    {
        return $this->belongsTo(HrDesignation::class, 'hr_designation_id');
    }

    public function candidates()
    {
        return $this->hasMany(HrCandidate::class);
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
