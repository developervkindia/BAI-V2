<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_id',
        'feedbackable_type',
        'feedbackable_id',
        'rating',
        'comment',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function feedbackable()
    {
        return $this->morphTo();
    }
}
