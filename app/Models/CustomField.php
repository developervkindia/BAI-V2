<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = ['board_id', 'name', 'type', 'options', 'position'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'position' => 'decimal:10',
        ];
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function values()
    {
        return $this->hasMany(CardCustomFieldValue::class);
    }
}
