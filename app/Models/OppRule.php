<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppRule extends Model
{
    protected $table = 'opp_rules';

    protected $fillable = [
        'project_id',
        'name',
        'is_active',
        'trigger_type',
        'trigger_config',
        'action_type',
        'action_config',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trigger_config' => 'array',
        'action_config' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(OppProject::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
