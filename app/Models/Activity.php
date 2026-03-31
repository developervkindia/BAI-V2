<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'board_id', 'card_id', 'subject_type', 'subject_id', 'action', 'data', 'created_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function getDescriptionAttribute(): string
    {
        $data = $this->data ?? [];
        return match ($this->action) {
            'created' => 'created this card',
            'updated' => 'updated ' . ($data['field'] ?? 'this card'),
            'moved' => 'moved this card from ' . ($data['from_list'] ?? '?') . ' to ' . ($data['to_list'] ?? '?'),
            'archived' => 'archived this card',
            'restored' => 'restored this card',
            'added_member' => 'added ' . ($data['member_name'] ?? 'a member'),
            'removed_member' => 'removed ' . ($data['member_name'] ?? 'a member'),
            'added_label' => 'added label ' . ($data['label_name'] ?? ''),
            'removed_label' => 'removed label ' . ($data['label_name'] ?? ''),
            'added_checklist' => 'added checklist ' . ($data['checklist_name'] ?? ''),
            'completed_checklist' => 'completed checklist ' . ($data['checklist_name'] ?? ''),
            'set_due_date' => 'set due date to ' . ($data['due_date'] ?? ''),
            'added_attachment' => 'attached ' . ($data['filename'] ?? 'a file'),
            default => $this->action,
        };
    }
}
