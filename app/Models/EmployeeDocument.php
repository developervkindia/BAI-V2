<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'type',
        'name',
        'document_number',
        'file_path',
        'expiry_date',
        'uploaded_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'document_number' => 'encrypted',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
