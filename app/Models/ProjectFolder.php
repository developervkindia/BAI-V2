<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'position',
        'created_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(ProjectFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProjectFolder::class, 'parent_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'project_folder_id');
    }
}
