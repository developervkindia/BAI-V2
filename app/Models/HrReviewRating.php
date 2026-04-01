<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrReviewRating extends Model
{
    use HasFactory;

    protected $table = 'hr_review_ratings';

    protected $fillable = [
        'hr_review_id',
        'hr_kra_id',
        'hr_goal_id',
        'rating',
        'comments',
    ];

    protected $casts = [
        'rating' => 'float',
    ];

    public function review()
    {
        return $this->belongsTo(HrReview::class, 'hr_review_id');
    }

    public function kra()
    {
        return $this->belongsTo(HrKra::class, 'hr_kra_id');
    }

    public function goal()
    {
        return $this->belongsTo(HrGoal::class, 'hr_goal_id');
    }
}
