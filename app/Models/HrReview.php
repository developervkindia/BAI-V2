<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrReview extends Model
{
    use HasFactory;

    protected $table = 'hr_reviews';

    protected $fillable = [
        'hr_review_cycle_id',
        'employee_profile_id',
        'reviewer_id',
        'review_type',
        'overall_rating',
        'strengths',
        'improvements',
        'comments',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'overall_rating' => 'float',
        'submitted_at' => 'datetime',
    ];

    public function reviewCycle()
    {
        return $this->belongsTo(HrReviewCycle::class, 'hr_review_cycle_id');
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function ratings()
    {
        return $this->hasMany(HrReviewRating::class);
    }
}
