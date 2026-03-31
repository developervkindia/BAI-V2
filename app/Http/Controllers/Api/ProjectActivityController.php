<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectActivity;
use App\Models\ProjectComment;
use App\Models\ProjectTask;

class ProjectActivityController extends Controller
{
    public function index(ProjectTask $task)
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        // Load activities
        $activities = $task->activities()
            ->with('user')
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'type'        => 'activity',
                'description' => $a->description,
                'user'        => ['id' => $a->user->id, 'name' => $a->user->name],
                'created_at'  => $a->created_at->diffForHumans(),
                'created_ts'  => $a->created_at->timestamp,
                'is_mine'     => false,
            ]);

        // Load comments
        $comments = $task->comments()
            ->with('user')
            ->get()
            ->map(fn($c) => [
                'id'          => $c->id,
                'type'        => 'comment',
                'description' => $c->content,
                'user'        => ['id' => $c->user->id, 'name' => $c->user->name],
                'created_at'  => $c->created_at->diffForHumans(),
                'created_ts'  => $c->created_at->timestamp,
                'is_mine'     => $c->user_id === auth()->id(),
            ]);

        // Merge and sort by timestamp desc
        $merged = $activities->merge($comments)
            ->sortByDesc('created_ts')
            ->values();

        return response()->json($merged);
    }
}
