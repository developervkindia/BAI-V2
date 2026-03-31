<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppDashboardWidget extends Model
{
    protected $table = 'opp_dashboard_widgets';

    protected $fillable = [
        'dashboard_id',
        'widget_type',
        'title',
        'config',
        'position',
        'size',
    ];

    protected $casts = [
        'config' => 'array',
        'position' => 'integer',
    ];

    public function dashboard()
    {
        return $this->belongsTo(OppDashboard::class, 'dashboard_id');
    }
}
