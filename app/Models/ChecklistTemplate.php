<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    protected $fillable = ['board_id', 'name', 'items'];

    protected function casts(): array
    {
        return ['items' => 'array'];
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
