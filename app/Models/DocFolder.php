<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocFolder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'parent_id',
        'created_by',
        'name',
        'slug',
        'color',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $folder) {
            if (empty($folder->slug) || $folder->isDirty('name')) {
                $base = Str::slug($folder->name) ?: 'folder';
                $slug = $base;
                $i = 1;
                while (
                    static::where('organization_id', $folder->organization_id)
                        ->where('parent_id', $folder->parent_id)
                        ->where('slug', $slug)
                        ->where('id', '!=', $folder->id ?? 0)
                        ->exists()
                ) {
                    $slug = $base . '-' . $i++;
                }
                $folder->slug = $slug;
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocDocument::class, 'folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        $org = auth()->user()?->currentOrganization();

        return $org
            ? $this->where('id', $value)->where('organization_id', $org->id)->first()
            : null;
    }
}
