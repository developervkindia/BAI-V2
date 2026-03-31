<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GithubTaskLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_task_id',
        'github_integration_id',
        'type',
        'github_url',
        'github_id',
        'title',
        'status',
        'author',
        'created_at_github',
    ];

    protected $casts = [
        'created_at_github' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function integration()
    {
        return $this->belongsTo(GithubIntegration::class, 'github_integration_id');
    }
}
