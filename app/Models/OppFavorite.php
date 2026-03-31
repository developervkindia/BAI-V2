<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppFavorite extends Model
{
    public $timestamps = false;

    protected $table = 'opp_favorites';

    protected $fillable = [
        'user_id',
        'favorable_type',
        'favorable_id',
        'position',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorable()
    {
        return $this->morphTo();
    }
}
