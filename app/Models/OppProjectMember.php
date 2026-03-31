<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OppProjectMember extends Model
{
    public $timestamps = false;

    protected $table = 'opp_project_members';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
    ];

    public function project()
    {
        return $this->belongsTo(OppProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
