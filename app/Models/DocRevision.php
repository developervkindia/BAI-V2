<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'user_id',
        'revision_number',
        'title',
        'body_html',
        'body_json',
        'snapshot_type',
    ];

    protected function casts(): array
    {
        return [
            'body_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocDocument::class, 'document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
