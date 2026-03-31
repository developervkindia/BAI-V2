<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OppForm extends Model
{
    protected $table = 'opp_forms';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'is_active',
        'is_public',
        'slug',
        'fields',
        'submit_action',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'fields' => 'array',
        'submit_action' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($form) {
            $form->slug = Str::slug($form->name) . '-' . Str::random(8);
        });
    }

    public function project()
    {
        return $this->belongsTo(OppProject::class, 'project_id');
    }

    public function submissions()
    {
        return $this->hasMany(OppFormSubmission::class, 'form_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
