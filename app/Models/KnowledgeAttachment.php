<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class KnowledgeAttachment extends Model
{
    public function resolveRouteBinding($value, $field = null)
    {
        $attachment = static::where($field ?? 'id', $value)->firstOrFail();
        $user = auth()->user();
        if ($user?->is_super_admin) {
            return $attachment;
        }
        $org = $user?->currentOrganization();
        abort_unless($org && (int) $attachment->organization_id === (int) $org->id, 404);

        return $attachment;
    }

    protected $fillable = [
        'organization_id',
        'knowledge_article_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function deleteFile(): void
    {
        if ($this->path && Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }
    }
}
