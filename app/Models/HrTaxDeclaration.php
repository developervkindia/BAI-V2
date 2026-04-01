<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrTaxDeclaration extends Model
{
    use HasFactory;

    protected $table = 'hr_tax_declarations';

    protected $fillable = [
        'organization_id',
        'employee_profile_id',
        'financial_year',
        'regime',
        'declarations',
        'status',
        'verified_by',
    ];

    protected $casts = [
        'declarations' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
