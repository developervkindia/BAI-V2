<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocFormResponse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'respondent_name',
        'respondent_email',
        'data',
        'ip_address',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocDocument::class, 'document_id');
    }
}
