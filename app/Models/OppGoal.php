<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OppGoal extends Model
{
    use SoftDeletes;

    protected $table = 'opp_goals';

    protected $fillable = [
        'organization_id',
        'parent_id',
        'owner_id',
        'title',
        'description',
        'goal_type',
        'metric_type',
        'target_value',
        'current_value',
        'status',
        'start_date',
        'due_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'target_value' => 'float',
        'current_value' => 'float',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function parent()
    {
        return $this->belongsTo(OppGoal::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OppGoal::class, 'parent_id');
    }

    public function links()
    {
        return $this->hasMany(OppGoalLink::class, 'goal_id');
    }

    public function getProgressAttribute()
    {
        if ($this->target_value > 0) {
            return ($this->current_value / $this->target_value) * 100;
        }

        return 0;
    }
}
