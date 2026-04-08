<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocAttachment extends Model
{
    protected $fillable = [
        'organization_id',
        'document_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocDocument::class, 'document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function deleteFile(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }

    public function getUrl(): string
    {
        return route('docs.files.show', $this);
    }
}
