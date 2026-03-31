<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProjectTimeLogController extends Controller
{
    public function index(ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        $logs = $task->timeLogs()->with('user')->orderByDesc('logged_at')->get()
            ->map(fn($l) => $this->formatLog($l));

        return response()->json([
            'logs'         => $logs,
            'total_logged' => round($task->timeLogs->sum('hours'), 2),
            'estimated'    => $task->estimated_hours,
        ]);
    }

    public function store(Request $request, ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        $data = $request->validate([
            'hours'       => 'required|numeric|min:0.25|max:99',
            'notes'       => 'nullable|string|max:500',
            'logged_at'   => 'nullable|date',
            'is_billable' => 'nullable|boolean',
        ]);

        $data['project_task_id'] = $task->id;
        $data['user_id']         = auth()->id();
        $data['logged_at']       = $data['logged_at'] ?? now()->toDateString();
        $data['is_billable']     = $data['is_billable'] ?? true;

        $log = ProjectTimeLog::create($data);
        $log->load('user');

        return response()->json([
            'log'          => $this->formatLog($log),
            'total_logged' => round($task->timeLogs()->sum('hours'), 2),
        ], 201);
    }

    public function update(Request $request, ProjectTimeLog $log): JsonResponse
    {
        abort_unless(
            $log->user_id === auth()->id() || $log->task->project->isManager(auth()->user()),
            403
        );

        if ($log->isRunning()) {
            return response()->json(['error' => 'Cannot edit a running timer.'], 422);
        }

        $data = $request->validate([
            'hours'       => 'sometimes|numeric|min:0|max:99',
            'notes'       => 'nullable|string|max:500',
            'is_billable' => 'sometimes|boolean',
        ]);

        $log->update($data);

        return response()->json([
            'success'      => true,
            'log'          => $this->formatLog($log->fresh('user')),
            'total_logged' => round($log->task->timeLogs()->sum('hours'), 2),
        ]);
    }

    public function destroy(ProjectTimeLog $log): JsonResponse
    {
        abort_unless(
            $log->user_id === auth()->id() || $log->task->project->isManager(auth()->user()),
            403
        );

        $task = $log->task;
        $log->delete();

        return response()->json([
            'success'      => true,
            'total_logged' => round($task->timeLogs()->sum('hours'), 2),
        ]);
    }

    // ── Timer ────────────────────────────────────────────────────────

    public function startTimer(Request $request, ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        // Check no active timer for this user
        $active = ProjectTimeLog::where('user_id', auth()->id())
            ->whereNotNull('timer_started_at')
            ->whereNull('timer_stopped_at')
            ->first();

        if ($active) {
            return response()->json([
                'error' => 'You already have a running timer.',
                'active_timer' => $this->formatLog($active->load('user')),
            ], 422);
        }

        $log = ProjectTimeLog::create([
            'project_task_id' => $task->id,
            'user_id'         => auth()->id(),
            'hours'           => 0,
            'logged_at'       => now()->toDateString(),
            'is_billable'     => true,
            'is_timer_entry'  => true,
            'timer_started_at'=> now(),
        ]);

        $log->load(['user', 'task.project']);

        return response()->json(['success' => true, 'timer' => $this->formatTimer($log)]);
    }

    public function stopTimer(Request $request, ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        $log = ProjectTimeLog::where('user_id', auth()->id())
            ->where('project_task_id', $task->id)
            ->whereNotNull('timer_started_at')
            ->whereNull('timer_stopped_at')
            ->first();

        if (!$log) {
            return response()->json(['error' => 'No running timer found.'], 404);
        }

        $log->timer_stopped_at = now();
        $log->hours = round(Carbon::parse($log->timer_started_at)->diffInMinutes(now()) / 60, 2);
        if ($log->hours < 0.01) $log->hours = 0.01;

        if ($request->has('notes')) $log->notes = $request->input('notes');
        if ($request->has('is_billable')) $log->is_billable = $request->boolean('is_billable');

        $log->save();
        $log->load('user');

        return response()->json([
            'success' => true,
            'log'     => $this->formatLog($log),
            'total_logged' => round($task->timeLogs()->sum('hours'), 2),
        ]);
    }

    public function activeTimer(Request $request): JsonResponse
    {
        $log = ProjectTimeLog::where('user_id', auth()->id())
            ->whereNotNull('timer_started_at')
            ->whereNull('timer_stopped_at')
            ->with(['user', 'task.project'])
            ->first();

        if (!$log) {
            return response()->json(['timer' => null]);
        }

        return response()->json(['timer' => $this->formatTimer($log)]);
    }

    // ── Formatters ───────────────────────────────────────────────────

    private function formatLog(ProjectTimeLog $l): array
    {
        return [
            'id'          => $l->id,
            'hours'       => $l->hours,
            'notes'       => $l->notes,
            'logged_at'   => $l->logged_at?->format('M j, Y'),
            'is_billable' => $l->is_billable,
            'is_timer'    => $l->is_timer_entry,
            'user'        => $l->user ? ['id' => $l->user->id, 'name' => $l->user->name] : null,
            'is_mine'     => $l->user_id === auth()->id(),
        ];
    }

    private function formatTimer(ProjectTimeLog $l): array
    {
        return [
            'id'               => $l->id,
            'task_id'          => $l->project_task_id,
            'task_title'       => $l->task?->title,
            'project_name'     => $l->task?->project?->name,
            'project_slug'     => $l->task?->project?->slug,
            'timer_started_at' => $l->timer_started_at->toISOString(),
            'user'             => $l->user ? ['id' => $l->user->id, 'name' => $l->user->name] : null,
        ];
    }
}
