<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardDependency extends Model
{
    public $timestamps = false;

    protected $fillable = ['card_id', 'depends_on_card_id', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function dependsOnCard()
    {
        return $this->belongsTo(Card::class, 'depends_on_card_id');
    }
}
