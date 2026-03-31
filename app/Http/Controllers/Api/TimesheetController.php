<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTimeLog;
use App\Models\TimesheetSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function submit(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $data = $request->validate([
            'week_start' => 'required|date',
            'week_end'   => 'required|date|after_or_equal:week_start',
        ]);

        // Calculate total hours from time logs
        $taskIds = $project->tasks()->pluck('id');
        $totalHours = ProjectTimeLog::whereIn('project_task_id', $taskIds)
            ->where('user_id', auth()->id())
            ->whereBetween('logged_at', [$data['week_start'], $data['week_end']])
            ->sum('hours');

        $submission = TimesheetSubmission::updateOrCreate(
            [
                'project_id' => $project->id,
                'user_id'    => auth()->id(),
                'week_start' => $data['week_start'],
            ],
            [
                'week_end'     => $data['week_end'],
                'total_hours'  => round($totalHours, 2),
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'submission' => $this->format($submission)]);
    }

    public function submissions(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $query = TimesheetSubmission::where('project_id', $project->id)
            ->with(['user', 'reviewer']);

        // Non-managers only see their own
        if (!$project->isManager(auth()->user())) {
            $query->where('user_id', auth()->id());
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $submissions = $query->orderByDesc('week_start')->get()
            ->map(fn($s) => $this->format($s));

        return response()->json(['submissions' => $submissions]);
    }

    public function approve(Request $request, TimesheetSubmission $submission): JsonResponse
    {
        abort_unless($submission->project->isManager(auth()->user()), 403);

        $submission->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return response()->json(['success' => true, 'submission' => $this->format($submission->fresh(['user', 'reviewer']))]);
    }

    public function reject(Request $request, TimesheetSubmission $submission): JsonResponse
    {
        abort_unless($submission->project->isManager(auth()->user()), 403);

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $submission->update([
            'status'           => 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return response()->json(['success' => true, 'submission' => $this->format($submission->fresh(['user', 'reviewer']))]);
    }

    private function format(TimesheetSubmission $s): array
    {
        return [
            'id'               => $s->id,
            'user'             => $s->user ? ['id' => $s->user->id, 'name' => $s->user->name] : null,
            'week_start'       => $s->week_start->format('Y-m-d'),
            'week_end'         => $s->week_end->format('Y-m-d'),
            'total_hours'      => $s->total_hours,
            'status'           => $s->status,
            'submitted_at'     => $s->submitted_at?->diffForHumans(),
            'reviewed_by'      => $s->reviewer ? ['id' => $s->reviewer->id, 'name' => $s->reviewer->name] : null,
            'reviewed_at'      => $s->reviewed_at?->diffForHumans(),
            'rejection_reason' => $s->rejection_reason,
        ];
    }
}
