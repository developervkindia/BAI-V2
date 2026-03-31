<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = ['card_id', 'name', 'position'];

    protected function casts(): array
    {
        return ['position' => 'decimal:10'];
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function items()
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('position');
    }

    public function getProgressAttribute(): int
    {
        $total = $this->items->count();
        if ($total === 0) return 0;
        return (int) round(($this->items->where('is_checked', true)->count() / $total) * 100);
    }
}
