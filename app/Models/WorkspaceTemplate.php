<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceTemplate extends Model
{
    protected $fillable = ['name', 'description', 'structure', 'created_by'];

    protected function casts(): array
    {
        return ['structure' => 'array'];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
