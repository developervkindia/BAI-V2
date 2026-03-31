<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'key', 'name', 'tagline', 'color', 'route_prefix', 'is_available', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'sort_order'   => 'integer',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(OrganizationSubscription::class);
    }
}
