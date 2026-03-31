<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'bio',
        'notification_preferences',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
            'is_super_admin' => 'boolean',
        ];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        return null;
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function ownedWorkspaces()
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function allWorkspaces()
    {
        return Workspace::where('owner_id', $this->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $this->id))
            ->with(['boards' => fn($q) => $q->where('is_archived', false)])
            ->get();
    }

    public function boards()
    {
        return $this->belongsToMany(Board::class, 'board_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function starredBoards()
    {
        return $this->belongsToMany(Board::class, 'board_stars')->withTimestamps();
    }

    public function canAccessBoard(int $boardId): bool
    {
        $board = Board::find($boardId);
        return $board && $board->canAccess($this);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest('created_at');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function watchedCards()
    {
        return $this->belongsToMany(Card::class, 'card_watchers')->withPivot('created_at');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function wantsEmailNotifications(): bool
    {
        $prefs = $this->notification_preferences ?? [];
        return $prefs['email_enabled'] ?? true;
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedOrganizations()
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function organizationRoles()
    {
        return $this->belongsToMany(Role::class, 'organization_member_roles')
            ->withPivot('organization_id');
    }

    public function employeeProfiles()
    {
        return $this->hasMany(EmployeeProfile::class);
    }

    public function employeeProfile(?int $orgId = null): ?EmployeeProfile
    {
        $orgId ??= $this->currentOrganization()?->id;
        if (!$orgId) return null;
        return $this->employeeProfiles()->where('organization_id', $orgId)->first();
    }

    public function allOrganizations(): \Illuminate\Support\Collection
    {
        if (isset($this->cachedOrgs)) {
            return $this->cachedOrgs;
        }
        $this->cachedOrgs = Organization::where('owner_id', $this->id)
            ->orWhereHas('members', fn ($q) => $q->where('user_id', $this->id))
            ->with(['subscriptions' => fn ($q) => $q->whereIn('status', ['active', 'trialing']), 'subscriptions.product'])
            ->get();
        return $this->cachedOrgs;
    }

    public function currentOrganization(): ?Organization
    {
        $orgId = session('current_org_id');
        if ($orgId) {
            $org = $this->allOrganizations()->firstWhere('id', $orgId);
            if ($org) {
                return $org;
            }
        }
        return $this->allOrganizations()->first();
    }
}
