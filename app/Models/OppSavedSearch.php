<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppSavedSearch extends Model
{
    protected $table = 'opp_saved_searches';

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'filters',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
