<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceGroup extends Model
{
    protected $fillable = ['workspace_id', 'name'];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'workspace_group_members', 'group_id');
    }
}
