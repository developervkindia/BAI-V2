<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrExpenseCategory extends Model
{
    use HasFactory;

    protected $table = 'hr_expense_categories';

    protected $fillable = [
        'organization_id',
        'name',
        'max_amount',
        'requires_receipt',
        'is_active',
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'requires_receipt' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
