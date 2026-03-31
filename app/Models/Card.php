<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'board_list_id',
        'board_id',
        'title',
        'description',
        'position',
        'cover_image_path',
        'cover_color',
        'due_date',
        'due_date_complete',
        'due_reminder',
        'start_date',
        'is_archived',
        'is_template',
        'mirrored_from_card_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'decimal:10',
            'is_archived' => 'boolean',
            'is_template' => 'boolean',
            'due_date' => 'datetime',
            'due_date_complete' => 'boolean',
            'start_date' => 'datetime',
        ];
    }

    public function boardList()
    {
        return $this->belongsTo(BoardList::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'card_members')->withTimestamps();
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'card_labels');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class)->orderBy('position');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)->latest('created_at');
    }

    public function watchers()
    {
        return $this->belongsToMany(User::class, 'card_watchers')->withPivot('created_at');
    }

    public function votes()
    {
        return $this->hasMany(CardVote::class);
    }

    public function customFieldValues()
    {
        return $this->hasMany(CardCustomFieldValue::class);
    }

    public function dependencies()
    {
        return $this->hasMany(CardDependency::class);
    }

    public function dependents()
    {
        return $this->hasMany(CardDependency::class, 'depends_on_card_id');
    }

    public function mirroredFrom()
    {
        return $this->belongsTo(Card::class, 'mirrored_from_card_id');
    }

    public function mirrors()
    {
        return $this->hasMany(Card::class, 'mirrored_from_card_id');
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeNotTemplates($query)
    {
        return $query->where('is_template', false);
    }

    public function isWatchedBy(User $user): bool
    {
        return $this->watchers()->where('user_id', $user->id)->exists();
    }

    public function isVotedBy(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function getVoteCountAttribute(): int
    {
        return $this->votes()->count();
    }

    public function getAgeDaysAttribute(): int
    {
        return (int) $this->updated_at->diffInDays(now());
    }

    public function getChecklistProgressAttribute(): array
    {
        $total = 0;
        $checked = 0;
        foreach ($this->checklists as $checklist) {
            $total += $checklist->items->count();
            $checked += $checklist->items->where('is_checked', true)->count();
        }
        return [
            'total' => $total,
            'checked' => $checked,
            'percent' => $total > 0 ? round(($checked / $total) * 100) : 0,
        ];
    }

    public function getDueStatusAttribute(): ?string
    {
        if (!$this->due_date) return null;
        if ($this->due_date_complete) return 'complete';
        if ($this->due_date->isPast()) return 'overdue';
        if ($this->due_date->diffInHours(now()) <= 24) return 'due_soon';
        return 'normal';
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image_path) {
            return asset('storage/' . $this->cover_image_path);
        }
        return null;
    }
}
