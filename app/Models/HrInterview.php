<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrInterview extends Model
{
    use HasFactory;

    protected $table = 'hr_interviews';

    protected $fillable = [
        'hr_candidate_id',
        'interviewer_id',
        'round',
        'scheduled_at',
        'duration_minutes',
        'mode',
        'status',
        'rating',
        'feedback',
        'decision',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function candidate()
    {
        return $this->belongsTo(HrCandidate::class, 'hr_candidate_id');
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
