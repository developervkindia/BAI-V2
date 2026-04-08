<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    public const STAGE_PROSPECT = 'prospect';

    public const STAGE_APPROVED = 'approved';

    public const STAGE_ACTIVE = 'active';

    public const STAGE_LOST = 'lost';

    protected $fillable = [
        'organization_id', 'name', 'company', 'email',
        'phone', 'timezone', 'notes', 'stage',
        'requirements_approved_at', 'hired_project_id',
    ];

    protected function casts(): array
    {
        return [
            'requirements_approved_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function hiredProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'hired_project_id');
    }

    public function portalUsers(): HasMany
    {
        return $this->hasMany(ClientPortalUser::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
    }

    public function stageLabel(): string
    {
        return match ($this->stage) {
            self::STAGE_PROSPECT => 'Pre-sales',
            self::STAGE_APPROVED => 'Approved',
            self::STAGE_ACTIVE => 'Active (delivery)',
            self::STAGE_LOST => 'Lost',
            default => ucfirst(str_replace('_', ' ', $this->stage)),
        };
    }
}
