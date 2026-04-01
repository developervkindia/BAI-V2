<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrAnnouncement extends Model
{
    use HasFactory;

    protected $table = 'hr_announcements';

    protected $fillable = [
        'organization_id',
        'title',
        'body',
        'type',
        'target_departments',
        'is_pinned',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'target_departments' => 'array',
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
