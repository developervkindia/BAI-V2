<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectComment;
use App\Models\ProjectTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectCommentController extends Controller
{
    public function index(ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        $comments = $task->comments()->with('user')->get()->map(fn ($c) => [
            'id'         => $c->id,
            'content'    => $c->content,
            'user'       => ['id' => $c->user->id, 'name' => $c->user->name],
            'created_at' => $c->created_at->diffForHumans(),
            'is_mine'    => $c->user_id === auth()->id(),
        ]);

        return response()->json(['comments' => $comments]);
    }

    public function store(Request $request, ProjectTask $task): JsonResponse
    {
        abort_unless($task->project->canAccess(auth()->user()), 403);

        $validated = $request->validate(['content' => 'required|string|max:5000']);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => [
                'id'         => $comment->id,
                'content'    => $comment->content,
                'user'       => ['id' => $comment->user->id, 'name' => $comment->user->name],
                'created_at' => 'just now',
                'is_mine'    => true,
            ],
        ]);
    }

    public function destroy(ProjectComment $comment): JsonResponse
    {
        abort_unless($comment->user_id === auth()->id(), 403);

        $comment->delete();

        return response()->json(['success' => true]);
    }
}
