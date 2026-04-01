<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrExitClearance extends Model
{
    use HasFactory;

    protected $table = 'hr_exit_clearances';

    protected $fillable = [
        'hr_exit_id',
        'department',
        'approver_id',
        'status',
        'remarks',
        'cleared_at',
    ];

    protected $casts = [
        'cleared_at' => 'datetime',
    ];

    public function exit()
    {
        return $this->belongsTo(HrExit::class, 'hr_exit_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
