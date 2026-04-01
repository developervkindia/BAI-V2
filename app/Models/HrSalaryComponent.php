<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSalaryComponent extends Model
{
    use HasFactory;

    protected $table = 'hr_salary_components';

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'type',
        'calculation_type',
        'percentage_of',
        'is_taxable',
        'is_statutory',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_statutory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
