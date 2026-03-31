<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class PositionService
{
    const GAP = 1024;
    const MIN_GAP = 0.001;

    public static function getNextPosition($query): float
    {
        $maxPosition = $query->max('position') ?? 0;
        return $maxPosition + self::GAP;
    }

    public static function getPositionBetween(?float $before, ?float $after): float
    {
        if ($before === null && $after === null) return self::GAP;
        if ($before === null) return $after / 2;
        if ($after === null) return $before + self::GAP;
        return ($before + $after) / 2;
    }

    public static function shouldRebalance(?float $before, ?float $after): bool
    {
        if ($before === null || $after === null) return false;
        return abs($after - $before) < self::MIN_GAP;
    }

    public static function rebalance($query, string $orderColumn = 'position'): array
    {
        $items = $query->orderBy($orderColumn)->get();
        $updates = [];
        foreach ($items as $index => $item) {
            $newPosition = ($index + 1) * self::GAP;
            $item->update([$orderColumn => $newPosition]);
            $updates[$item->id] = $newPosition;
        }
        return $updates;
    }

    public static function calculatePosition($query, ?int $afterId, ?int $beforeId, string $orderColumn = 'position'): float
    {
        $beforePos = null;
        $afterPos = null;

        if ($afterId) {
            $afterPos = $query->where('id', $afterId)->value($orderColumn);
        }
        if ($beforeId) {
            $beforePos = $query->where('id', $beforeId)->value($orderColumn);
        }

        $position = self::getPositionBetween($afterPos, $beforePos);

        if (self::shouldRebalance($afterPos, $beforePos)) {
            self::rebalance($query, $orderColumn);
            return self::calculatePosition($query, $afterId, $beforeId, $orderColumn);
        }

        return $position;
    }
}
