<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KnowledgeArticle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'knowledge_category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body_html',
        'status',
        'published_at',
        'pinned',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'pinned' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (KnowledgeArticle $article) {
            if (empty($article->slug) && ! empty($article->title)) {
                $article->slug = static::uniqueSlugForOrg($article->organization_id, $article->title, $article->id);
            }
        });
    }

    public static function uniqueSlugForOrg(int $organizationId, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'article';
        $slug = $base;
        $n = 1;
        while (static::query()
            ->withTrashed()
            ->where('organization_id', $organizationId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= 'slug';
        $org = auth()->user()?->currentOrganization();
        abort_unless($org, 404);

        $query = static::query()->where('organization_id', $org->id);

        if ($field === 'slug') {
            $asInt = filter_var($value, FILTER_VALIDATE_INT);
            if ($asInt !== false && $asInt > 0) {
                return $query->where('id', $asInt)->firstOrFail();
            }
        }

        return $query->where($field, $value)->firstOrFail();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'knowledge_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(KnowledgeArticleRevision::class, 'knowledge_article_id')->orderByDesc('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(KnowledgeAttachment::class, 'knowledge_article_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(KnowledgeTag::class, 'knowledge_article_tag', 'knowledge_article_id', 'knowledge_tag_id');
    }
}
