<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'owner_id', 'client_id', 'name', 'slug', 'description',
        'status', 'priority', 'color', 'start_date', 'end_date', 'visibility',
        'project_type', 'budget', 'hourly_rate',
        'srs_url', 'design_url', 'design_status',
        'design_approved_by', 'design_approved_at', 'design_feedback',
    ];

    protected function casts(): array
    {
        return [
            'start_date'        => 'date',
            'end_date'          => 'date',
            'design_approved_at'=> 'datetime',
            'budget'            => 'decimal:2',
            'hourly_rate'       => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name) . '-' . Str::random(6);
            }
        });

        static::created(function (Project $project) {
            ProjectStatus::seedDefaults($project);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function statuses()
    {
        return $this->hasMany(ProjectStatus::class)->orderBy('position');
    }

    public function customFields()
    {
        return $this->hasMany(ProjectCustomField::class)->orderBy('position');
    }

    public function savedViews()
    {
        return $this->hasMany(ProjectSavedView::class);
    }

    public function messages()
    {
        return $this->hasMany(ProjectMessage::class)->latest();
    }

    public function folders()
    {
        return $this->hasMany(ProjectFolder::class);
    }

    public function recurringTaskPatterns()
    {
        return $this->hasMany(RecurringTaskPattern::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function taskLists()
    {
        return $this->hasMany(TaskList::class)->orderBy('position');
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function labels()
    {
        return $this->hasMany(ProjectLabel::class);
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class)->orderBy('created_at');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeChanges()
    {
        return $this->hasMany(ProjectScopeChange::class)->orderByDesc('created_at');
    }

    public function weeklyUpdates()
    {
        return $this->hasMany(ProjectWeeklyUpdate::class)->orderByDesc('week_start');
    }

    public function billingWeeks()
    {
        return $this->hasMany(ProjectBillingWeek::class)->orderByDesc('week_start');
    }

    public function canAccess(User $user): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }
        if ($this->members()->where('user_id', $user->id)->exists()) {
            return true;
        }
        // Organization members can access organization-visible projects
        if ($this->visibility === 'organization') {
            return $this->organization->hasUser($user);
        }
        return false;
    }

    public function canEdit(User $user): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }
        return $this->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['manager', 'member'])
            ->exists();
    }

    public function isManager(User $user): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }
        return $this->members()
            ->where('user_id', $user->id)
            ->where('role', 'manager')
            ->exists();
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->whereNull('parent_task_id')->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $this->tasks()->whereNull('parent_task_id')->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'in_progress'  => 'blue',
            'completed'    => 'green',
            'on_hold'      => 'amber',
            'cancelled'    => 'red',
            default        => 'gray',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'red',
            'high'     => 'orange',
            'medium'   => 'amber',
            'low'      => 'blue',
            default    => 'gray',
        };
    }
}
