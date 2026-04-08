<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeArticleRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'knowledge_article_id', 'user_id', 'title', 'body_html', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
