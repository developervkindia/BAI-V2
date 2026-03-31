<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppGoalLink extends Model
{
    protected $table = 'opp_goal_links';

    protected $fillable = [
        'goal_id',
        'linkable_type',
        'linkable_id',
    ];

    public function goal()
    {
        return $this->belongsTo(OppGoal::class, 'goal_id');
    }

    public function linkable()
    {
        return $this->morphTo();
    }
}
