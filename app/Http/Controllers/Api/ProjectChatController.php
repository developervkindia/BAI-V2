<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMessage;
use Illuminate\Http\Request;

class ProjectChatController extends Controller
{
    public function index(Project $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $messages = $project->messages()
            ->with('user')
            ->latest()
            ->cursorPaginate(50);

        return response()->json($messages);
    }

    public function store(Request $request, Project $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = $project->messages()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $message->load('user');

        return response()->json($message, 201);
    }

    public function destroy(ProjectMessage $message)
    {
        abort_unless(
            $message->user_id === auth()->id() || $message->project->isManager(auth()->user()),
            403
        );

        $message->delete();

        return response()->json(['message' => 'Message deleted.']);
    }
}
