<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class KnowledgeTag extends Model
{
    protected $fillable = [
        'organization_id', 'name', 'slug',
    ];

    protected static function booted(): void
    {
        static::saving(function (KnowledgeTag $tag) {
            if (empty($tag->slug) && ! empty($tag->name)) {
                $tag->slug = static::uniqueSlugForOrg($tag->organization_id, $tag->name, $tag->id);
            }
        });
    }

    public static function uniqueSlugForOrg(int $organizationId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'tag';
        $slug = $base;
        $n = 1;
        while (static::query()
            ->where('organization_id', $organizationId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(KnowledgeArticle::class, 'knowledge_article_tag', 'knowledge_tag_id', 'knowledge_article_id');
    }
}
