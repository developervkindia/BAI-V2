<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Carbon;

class HubDashboardService
{
    /**
     * Gather quick stats across all products for the hub dashboard.
     */
    public function getQuickStats(User $user, Organization $org): array
    {
        $stats = [];

        // Board stats
        $boardCount = \App\Models\Board::whereHas('workspace', fn ($q) => $q->where('organization_id', $org->id))
            ->where('is_archived', false)->count();
        $stats['boards'] = $boardCount;

        // Projects stats
        $projectCount = \App\Models\Project::where('organization_id', $org->id)
            ->where('status', 'active')->count();
        $stats['active_projects'] = $projectCount;

        // Opportunity stats
        $oppTasksDue = \App\Models\OppTask::whereHas('section.project', fn ($q) => $q->where('organization_id', $org->id))
            ->whereNull('completed_at')
            ->where('due_date', '<=', Carbon::today()->addDays(7))
            ->count();
        $stats['opp_tasks_due_soon'] = $oppTasksDue;

        // HR stats
        $employeeCount = \App\Models\EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')->count();
        $pendingLeaves = \App\Models\HrLeaveRequest::whereHas('employeeProfile', fn ($q) => $q->where('organization_id', $org->id))
            ->where('status', 'pending')->count();
        $stats['employees'] = $employeeCount;
        $stats['pending_leaves'] = $pendingLeaves;

        return $stats;
    }

    /**
     * Get recent activity across products.
     */
    public function getRecentActivity(User $user, Organization $org, int $limit = 10): array
    {
        $activities = [];

        // Recent board activity (cards created/moved)
        $recentCards = \App\Models\Card::whereHas('boardList.board.workspace', fn ($q) => $q->where('organization_id', $org->id))
            ->latest('updated_at')
            ->limit($limit)
            ->get(['id', 'title', 'updated_at'])
            ->map(fn ($c) => [
                'type' => 'board',
                'icon' => 'card',
                'text' => "Card updated: {$c->title}",
                'time' => $c->updated_at,
            ]);
        $activities = array_merge($activities, $recentCards->toArray());

        // Recent project tasks
        $recentTasks = \App\Models\ProjectTask::whereHas('project', fn ($q) => $q->where('organization_id', $org->id))
            ->latest('updated_at')
            ->limit($limit)
            ->get(['id', 'title', 'updated_at'])
            ->map(fn ($t) => [
                'type' => 'projects',
                'icon' => 'task',
                'text' => "Task updated: {$t->title}",
                'time' => $t->updated_at,
            ]);
        $activities = array_merge($activities, $recentTasks->toArray());

        // Recent HR announcements
        $recentAnnouncements = \App\Models\HrAnnouncement::where('organization_id', $org->id)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->limit(3)
            ->get(['id', 'title', 'published_at'])
            ->map(fn ($a) => [
                'type' => 'hr',
                'icon' => 'announcement',
                'text' => "Announcement: {$a->title}",
                'time' => $a->published_at,
            ]);
        $activities = array_merge($activities, $recentAnnouncements->toArray());

        // Sort by time descending, take limit
        usort($activities, fn ($a, $b) => strtotime($b['time'] ?? 0) - strtotime($a['time'] ?? 0));

        return array_slice($activities, 0, $limit);
    }
}
