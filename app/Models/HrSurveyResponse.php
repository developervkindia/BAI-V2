<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSurveyResponse extends Model
{
    use HasFactory;

    protected $table = 'hr_survey_responses';

    protected $fillable = [
        'hr_survey_id',
        'hr_survey_question_id',
        'employee_profile_id',
        'answer',
        'rating',
    ];

    public function survey()
    {
        return $this->belongsTo(HrSurvey::class, 'hr_survey_id');
    }

    public function question()
    {
        return $this->belongsTo(HrSurveyQuestion::class, 'hr_survey_question_id');
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
