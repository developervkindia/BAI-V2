<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardVote extends Model
{
    public $timestamps = false;

    protected $fillable = ['card_id', 'user_id', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
