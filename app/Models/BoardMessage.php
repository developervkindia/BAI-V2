<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardMessage extends Model
{
    protected $fillable = ['board_id', 'user_id', 'body'];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
