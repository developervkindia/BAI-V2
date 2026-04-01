<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrExpenseItem extends Model
{
    use HasFactory;

    protected $table = 'hr_expense_items';

    protected $fillable = [
        'hr_expense_claim_id',
        'hr_expense_category_id',
        'description',
        'amount',
        'expense_date',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function claim()
    {
        return $this->belongsTo(HrExpenseClaim::class, 'hr_expense_claim_id');
    }

    public function category()
    {
        return $this->belongsTo(HrExpenseCategory::class, 'hr_expense_category_id');
    }
}
