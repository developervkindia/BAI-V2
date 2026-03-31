<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppActivityLog;
use App\Models\OppComment;
use Illuminate\Http\Request;

class OppCommentController extends Controller
{
    /**
     * List comments for a task or project.
     */
    public function index(Request $request)
    {
        $request->validate([
            'task_id' => 'required_without:project_id|nullable|exists:opp_tasks,id',
            'project_id' => 'required_without:task_id|nullable|exists:opp_projects,id',
        ]);

        $query = OppComment::with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->orderByDesc('created_at');

        if ($request->task_id) {
            $query->where('task_id', $request->task_id);
        } elseif ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        $comments = $query->get();

        return response()->json(['comments' => $comments]);
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'task_id' => 'required_without:project_id|nullable|exists:opp_tasks,id',
            'project_id' => 'required_without:task_id|nullable|exists:opp_projects,id',
            'parent_id' => 'nullable|exists:opp_comments,id',
        ]);

        $comment = OppComment::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        if ($comment->task_id) {
            OppActivityLog::create([
                'user_id'    => auth()->id(),
                'task_id'    => $comment->task_id,
                'project_id' => $comment->project_id ?? $comment->task?->project_id,
                'action'     => 'comment.added',
                'new_value'  => \Illuminate\Support\Str::limit($comment->body, 100),
                'created_at' => now(),
            ]);
        }

        $comment->load('user');

        return response()->json([
            'comment' => [
                'id'         => $comment->id,
                'body'       => $comment->body,
                'user'       => ['id' => $comment->user->id, 'name' => $comment->user->name],
                'created_at' => $comment->created_at?->toISOString(),
                'edited_at'  => $comment->edited_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * Update a comment (own comments only).
     */
    public function update(Request $request, OppComment $comment)
    {
        abort_unless($comment->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $comment->update(array_merge($validated, [
            'edited_at' => now(),
        ]));

        return response()->json(['comment' => $comment->fresh('user')]);
    }

    /**
     * Soft delete a comment (own comments or project owner).
     */
    public function destroy(OppComment $comment)
    {
        $isOwner = $comment->user_id === auth()->id();
        $isProjectOwner = false;

        if ($comment->project_id) {
            $isProjectOwner = $comment->project
                && $comment->project->owner_id === auth()->id();
        } elseif ($comment->task_id && $comment->task) {
            $isProjectOwner = $comment->task->project
                && $comment->task->project->owner_id === auth()->id();
        }

        abort_unless($isOwner || $isProjectOwner, 403);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted.']);
    }
}
