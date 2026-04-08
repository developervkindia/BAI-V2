<?php

namespace App\Models;

use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'logo_path', 'owner_id',
        'is_active', 'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted(): void
    {
        static::creating(function (Organization $org) {
            if (empty($org->slug)) {
                $org->slug = Str::slug($org->name).'-'.Str::random(6);
            }
        });

        static::created(function (Organization $org) {
            PermissionSeeder::seedRolesForOrg($org);
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
        return $this->belongsToMany(User::class, 'organization_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function invitations()
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    public function employeeProfiles()
    {
        return $this->hasMany(EmployeeProfile::class);
    }

    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    public function activeSubscriptions()
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->with('product');
    }

    public function hasProduct(string $productKey): bool
    {
        return $this->activeSubscriptions()
            ->whereHas('product', fn ($q) => $q->where('key', $productKey))
            ->exists();
    }

    public function hasUser(User $user): bool
    {
        return $this->owner_id === $user->id
            || $this->members()->where('user_id', $user->id)->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function isAdmin(User $user): bool
    {
        return $this->owner_id === $user->id
            || $this->members()
                ->where('user_id', $user->id)
                ->whereIn('role', ['owner', 'admin'])
                ->exists();
    }
}
