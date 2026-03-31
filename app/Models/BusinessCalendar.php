<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'is_default',
        'working_days',
    ];

    protected $casts = [
        'working_days' => 'array',
        'is_default' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }
}
