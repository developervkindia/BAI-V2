<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Board extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'background_type',
        'background_value',
        'visibility',
        'email_address',
        'is_archived',
        'closed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_archived' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'board_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function lists()
    {
        return $this->hasMany(BoardList::class)->orderBy('position');
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function stars()
    {
        return $this->hasMany(BoardStar::class);
    }

    public function isStarredBy(User $user): bool
    {
        return $this->stars()->where('user_id', $user->id)->exists();
    }

    /**
     * Can this user see the board at all?
     */
    public function canAccess(User $user): bool
    {
        if ($this->visibility === 'public') return true;
        if ($this->members()->where('user_id', $user->id)->exists()) return true;
        if ($this->visibility === 'workspace') {
            return $this->workspace->hasUser($user);
        }
        return false;
    }

    /**
     * Can this user create/edit/delete cards, lists, comments, attachments?
     * Returns true for admin + normal members. False for observers and non-members.
     */
    public function canEdit(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['admin', 'normal'])
            ->exists();
    }

    /**
     * Can this user manage board settings, members, delete the board?
     * Returns true for board admins and workspace owner only.
     */
    public function isAdmin(User $user): bool
    {
        if ($this->created_by === $user->id) return true;
        return $this->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Get the role of a user on this board. Returns null if not a direct member.
     */
    public function getUserRole(User $user): ?string
    {
        $member = $this->members()->where('user_id', $user->id)->first();
        return $member?->pivot?->role;
    }

    public function customFields()
    {
        return $this->hasMany(CustomField::class)->orderBy('position');
    }

    public function checklistTemplates()
    {
        return $this->hasMany(ChecklistTemplate::class);
    }

    public function cardTemplates()
    {
        return $this->hasMany(Card::class)->where('is_template', true);
    }

    public function isObserver(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->where('role', 'observer')->exists();
    }

    public function getBackgroundStyleAttribute(): string
    {
        if ($this->background_type === 'image') {
            return "background-image: url('" . asset('storage/' . $this->background_value) . "'); background-size: cover; background-position: center;";
        }
        return "background: {$this->background_value};";
    }
}
