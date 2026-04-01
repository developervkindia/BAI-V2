<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrKra extends Model
{
    use HasFactory;

    protected $table = 'hr_kras';

    protected $fillable = [
        'organization_id',
        'hr_designation_id',
        'title',
        'description',
        'weightage',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function designation()
    {
        return $this->belongsTo(HrDesignation::class, 'hr_designation_id');
    }
}
