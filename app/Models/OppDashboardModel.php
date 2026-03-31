<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppDashboard extends Model
{
    protected $table = 'opp_dashboards';

    protected $fillable = [
        'organization_id',
        'name',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function widgets()
    {
        return $this->hasMany(OppDashboardWidget::class, 'dashboard_id')->orderBy('position');
    }
}
