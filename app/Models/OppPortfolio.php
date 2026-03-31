<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppPortfolio extends Model
{
    protected $table = 'opp_portfolios';

    protected $fillable = [
        'organization_id',
        'name',
        'owner_id',
        'color',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function projects()
    {
        return $this->belongsToMany(OppProject::class, 'opp_portfolio_projects', 'portfolio_id', 'project_id')
            ->withPivot('position');
    }
}
