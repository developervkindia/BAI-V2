<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationSubscription extends Model
{
    protected $fillable = [
        'organization_id', 'product_id', 'plan', 'status',
        'trial_ends_at', 'starts_at', 'ends_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'starts_at'     => 'datetime',
            'ends_at'       => 'datetime',
            'metadata'      => 'array',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isActive(): bool
    {
        if (!in_array($this->status, ['active', 'trialing'])) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }
        return true;
    }
}
