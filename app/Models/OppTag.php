<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppTag extends Model
{
    protected $table = 'opp_tags';

    protected $fillable = [
        'organization_id',
        'name',
        'color',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(OppTask::class, 'opp_task_tags', 'tag_id', 'task_id');
    }
}
