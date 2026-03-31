<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppSection extends Model
{
    protected $table = 'opp_sections';

    protected $fillable = [
        'project_id',
        'name',
        'position',
    ];

    protected $casts = [
        'position' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(OppProject::class, 'project_id');
    }

    public function tasks()
    {
        return $this->hasMany(OppTask::class, 'section_id')->orderBy('position');
    }
}
