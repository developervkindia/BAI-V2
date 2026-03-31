<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPortalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'is_portal_enabled',
        'show_tasks',
        'show_milestones',
        'show_files',
        'show_updates',
        'show_billing',
    ];

    protected $casts = [
        'is_portal_enabled' => 'boolean',
        'show_tasks' => 'boolean',
        'show_milestones' => 'boolean',
        'show_files' => 'boolean',
        'show_updates' => 'boolean',
        'show_billing' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
