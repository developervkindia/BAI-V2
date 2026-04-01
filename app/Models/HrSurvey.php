<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSurvey extends Model
{
    use HasFactory;

    protected $table = 'hr_surveys';

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'type',
        'is_anonymous',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function questions()
    {
        return $this->hasMany(HrSurveyQuestion::class)->orderBy('sort_order');
    }

    public function responses()
    {
        return $this->hasMany(HrSurveyResponse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
