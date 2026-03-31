<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdminAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'target_type', 'target_id', 'metadata', 'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(User $admin, string $action, ?Model $target = null, array $metadata = []): self
    {
        return static::create([
            'user_id'     => $admin->id,
            'action'      => $action,
            'target_type' => $target ? get_class($target) : null,
            'target_id'   => $target?->id,
            'metadata'    => $metadata ?: null,
            'ip_address'  => request()?->ip(),
            'created_at'  => now(),
        ]);
    }
}
