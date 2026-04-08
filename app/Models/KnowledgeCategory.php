<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class KnowledgeCategory extends Model
{
    protected $fillable = [
        'organization_id', 'name', 'slug', 'description', 'icon', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (KnowledgeCategory $category) {
            if (empty($category->slug) && ! empty($category->name)) {
                $category->slug = static::uniqueSlugForOrg($category->organization_id, $category->name, $category->id);
            }
        });
    }

    public static function uniqueSlugForOrg(int $organizationId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class, 'knowledge_category_id');
    }
}
