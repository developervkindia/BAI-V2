<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrCandidate extends Model
{
    use HasFactory;

    protected $table = 'hr_candidates';

    protected $fillable = [
        'organization_id',
        'hr_job_posting_id',
        'name',
        'email',
        'phone',
        'resume_path',
        'source',
        'stage',
        'current_company',
        'current_designation',
        'experience_years',
        'expected_ctc',
        'notes',
        'referred_by',
    ];

    protected $casts = [
        'experience_years' => 'float',
        'expected_ctc' => 'decimal:2',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function jobPosting()
    {
        return $this->belongsTo(HrJobPosting::class, 'hr_job_posting_id');
    }

    public function interviews()
    {
        return $this->hasMany(HrInterview::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
}
