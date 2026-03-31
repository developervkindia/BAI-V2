<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'board_id',
        'name',
        'position',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'decimal:10',
            'is_archived' => 'boolean',
        ];
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class)->orderBy('position');
    }

    public function activeCards()
    {
        return $this->cards()->where('is_archived', false);
    }
}
