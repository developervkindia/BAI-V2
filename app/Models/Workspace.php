<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_path',
        'visibility',
        'owner_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Workspace $workspace) {
            if (empty($workspace->slug)) {
                $workspace->slug = Str::slug($workspace->name) . '-' . Str::random(6);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    public function hasUser(User $user): bool
    {
        return $this->owner_id === $user->id || $this->members()->where('user_id', $user->id)->exists();
    }

    public function isAdmin(User $user): bool
    {
        return $this->owner_id === $user->id || $this->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }

    public function groups()
    {
        return $this->hasMany(WorkspaceGroup::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
