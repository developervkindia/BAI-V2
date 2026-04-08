<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'folder_id',
        'owner_id',
        'type',
        'title',
        'slug',
        'description',
        'body_html',
        'body_json',
        'settings',
        'status',
        'sharing_mode',
        'sharing_token',
        'version',
        'is_template',
        'word_count',
        'last_edited_by',
        'last_edited_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'body_json' => 'array',
            'settings' => 'array',
            'is_template' => 'boolean',
            'last_edited_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $doc) {
            if (empty($doc->slug) || $doc->isDirty('title')) {
                $base = Str::slug($doc->title) ?: $doc->type;
                $slug = $base;
                $i = 1;
                while (
                    static::where('organization_id', $doc->organization_id)
                        ->where('slug', $slug)
                        ->where('id', '!=', $doc->id ?? 0)
                        ->withTrashed()
                        ->exists()
                ) {
                    $slug = $base . '-' . $i++;
                }
                $doc->slug = $slug;
            }

            if ($doc->type === 'document' && $doc->isDirty('body_html')) {
                $doc->word_count = str_word_count(strip_tags($doc->body_html ?? ''));
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocFolder::class, 'folder_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(DocShare::class, 'document_id');
    }

    public function stars(): HasMany
    {
        return $this->hasMany(DocStar::class, 'document_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocRevision::class, 'document_id')->orderByDesc('created_at');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DocComment::class, 'document_id');
    }

    public function formResponses(): HasMany
    {
        return $this->hasMany(DocFormResponse::class, 'document_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DocAttachment::class, 'document_id');
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('owner_id', $user->id)
              ->orWhereHas('shares', fn (Builder $sq) => $sq->where('user_id', $user->id))
              ->orWhereIn('sharing_mode', ['org_view', 'org_edit']);
        });
    }

    public function scopeInFolder(Builder $query, ?int $folderId): Builder
    {
        return $folderId ? $query->where('folder_id', $folderId) : $query->whereNull('folder_id');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    public function isSpreadsheet(): bool
    {
        return $this->type === 'spreadsheet';
    }

    public function isForm(): bool
    {
        return $this->type === 'form';
    }

    public function isPresentation(): bool
    {
        return $this->type === 'presentation';
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function userCan(User $user, string $permission): bool
    {
        if ($this->isOwnedBy($user)) {
            return true;
        }

        $share = $this->shares()->where('user_id', $user->id)->first();
        if ($share) {
            return match ($permission) {
                'view' => true,
                'comment' => in_array($share->permission, ['comment', 'edit']),
                'edit' => $share->permission === 'edit',
                default => false,
            };
        }

        // Org-level sharing
        return match ($permission) {
            'view' => in_array($this->sharing_mode, ['org_view', 'org_edit']),
            'edit' => $this->sharing_mode === 'org_edit',
            default => false,
        };
    }

    public function isStarredBy(User $user): bool
    {
        return $this->stars()->where('user_id', $user->id)->exists();
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        $org = auth()->user()?->currentOrganization();

        if ($org) {
            return $this->where('id', $value)->where('organization_id', $org->id)->first();
        }

        // Fallback: find by ID and verify user has access via any org
        $doc = $this->where('id', $value)->first();
        if ($doc && auth()->check()) {
            $userOrgIds = auth()->user()->allOrganizations()->pluck('id');
            if ($userOrgIds->contains($doc->organization_id)) {
                return $doc;
            }
        }

        return null;
    }

    public function getEditorRoute(): string
    {
        return match ($this->type) {
            'document' => route('docs.documents.show', $this),
            'spreadsheet' => route('docs.spreadsheets.show', $this),
            'form' => route('docs.forms.show', $this),
            'presentation' => route('docs.presentations.show', $this),
        };
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'spreadsheet' => 'M3 10h18M3 14h18M3 6h18M3 18h18M7 3v18M17 3v18',
            'form' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'presentation' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
        };
    }

    public function getTypeColor(): string
    {
        return match ($this->type) {
            'document' => '#0EA5E9',     // sky
            'spreadsheet' => '#22C55E',  // green
            'form' => '#A855F7',         // purple
            'presentation' => '#F59E0B', // amber
        };
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'document' => 'Document',
            'spreadsheet' => 'Spreadsheet',
            'form' => 'Form',
            'presentation' => 'Presentation',
        };
    }
}
