<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OppProject extends Model
{
    use SoftDeletes;

    protected $table = 'opp_projects';

    protected $fillable = [
        'organization_id',
        'owner_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'visibility',
        'status',
        'start_date',
        'due_date',
        'is_template',
        'template_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'is_template' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (OppProject $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name) . '-' . Str::random(6);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sections()
    {
        return $this->hasMany(OppSection::class, 'project_id')->orderBy('position');
    }

    public function tasks()
    {
        return $this->hasMany(OppTask::class, 'project_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'opp_project_members', 'project_id', 'user_id')
            ->withPivot('role');
    }

    public function comments()
    {
        return $this->hasMany(OppComment::class, 'project_id');
    }

    public function customFields()
    {
        return $this->belongsToMany(OppCustomField::class, 'opp_project_custom_fields', 'project_id', 'custom_field_id')
            ->withPivot('position');
    }

    public function canAccess(User $user): bool
    {
        return $this->owner_id === $user->id
            || $this->members()->where('user_id', $user->id)->exists()
            || $this->visibility === 'public';
    }

    public function canEdit(User $user): bool
    {
        return $this->owner_id === $user->id
            || $this->members()->where('user_id', $user->id)->whereIn('role', ['owner', 'editor'])->exists();
    }
}
