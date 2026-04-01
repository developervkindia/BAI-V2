<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSalaryStructureComponent extends Model
{
    use HasFactory;

    protected $table = 'hr_salary_structure_components';

    protected $fillable = [
        'hr_salary_structure_id',
        'hr_salary_component_id',
        'monthly_amount',
        'annual_amount',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'annual_amount' => 'decimal:2',
    ];

    public function structure()
    {
        return $this->belongsTo(HrSalaryStructure::class, 'hr_salary_structure_id');
    }

    public function component()
    {
        return $this->belongsTo(HrSalaryComponent::class, 'hr_salary_component_id');
    }
}
