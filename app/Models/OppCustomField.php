<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppCustomField extends Model
{
    protected $table = 'opp_custom_fields';

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'options',
        'is_required',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function projects()
    {
        return $this->belongsToMany(OppProject::class, 'opp_project_custom_fields', 'custom_field_id', 'project_id');
    }
}
