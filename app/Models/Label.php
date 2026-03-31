<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = ['board_id', 'name', 'color'];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_labels');
    }

    public static function defaultColors(): array
    {
        return [
            'green' => '#22c55e',
            'yellow' => '#eab308',
            'orange' => '#f97316',
            'red' => '#ef4444',
            'purple' => '#a855f7',
            'blue' => '#3b82f6',
            'sky' => '#0ea5e9',
            'lime' => '#84cc16',
            'pink' => '#ec4899',
            'black' => '#1f2937',
        ];
    }
}
