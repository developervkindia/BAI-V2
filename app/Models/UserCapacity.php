<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCapacity extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'weekly_capacity_hours',
        'effective_from', 'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'weekly_capacity_hours' => 'float',
            'effective_from'        => 'date',
            'effective_until'       => 'date',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getForUser(int $userId, int $projectId, $date = null): ?self
    {
        $date = $date ?? now()->toDateString();

        return static::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')->orWhere('effective_until', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }
}
