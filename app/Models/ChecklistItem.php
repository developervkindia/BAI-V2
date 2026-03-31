<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['checklist_id', 'content', 'is_checked', 'position', 'assigned_to', 'due_date'];

    protected function casts(): array
    {
        return [
            'is_checked' => 'boolean',
            'position' => 'decimal:10',
            'due_date' => 'datetime',
        ];
    }

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
