<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrReviewCycle extends Model
{
    use HasFactory;

    protected $table = 'hr_review_cycles';

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'self_review_deadline',
        'manager_review_deadline',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'self_review_deadline' => 'date',
        'manager_review_deadline' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function reviews()
    {
        return $this->hasMany(HrReview::class);
    }

    public function goals()
    {
        return $this->hasMany(HrGoal::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
