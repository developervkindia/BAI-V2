<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSurveyQuestion extends Model
{
    use HasFactory;

    protected $table = 'hr_survey_questions';

    protected $fillable = [
        'hr_survey_id',
        'question',
        'type',
        'options',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(HrSurvey::class, 'hr_survey_id');
    }

    public function responses()
    {
        return $this->hasMany(HrSurveyResponse::class);
    }
}
