<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Card;
use App\Models\Comment;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Card $card)
    {
        abort_unless($card->board->canAccess(auth()->user()), 403);
        return response()->json($card->comments()->with('user')->get());
    }

    public function store(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $validated = $request->validate(['body' => 'required|string|max:5000']);

        $comment = Comment::create([
            'card_id' => $card->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        Activity::create([
            'user_id' => auth()->id(),
            'board_id' => $card->board_id,
            'card_id' => $card->id,
            'subject_type' => Comment::class,
            'subject_id' => $comment->id,
            'action' => 'commented',
            'data' => ['preview' => \Str::limit($validated['body'], 100)],
            'created_at' => now(),
        ]);

        // Notify card stakeholders about the comment
        NotificationService::notifyCardStakeholders(
            $card, 'comment',
            auth()->user()->name . ' commented on "' . $card->title . '"',
            \Str::limit($validated['body'], 200),
            auth()->id()
        );

        // Handle @mentions
        NotificationService::notifyMentions($validated['body'], $card, auth()->user());

        return response()->json($comment->load('user'), 201);
    }

    public function update(Request $request, Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);

        $validated = $request->validate(['body' => 'required|string|max:5000']);
        $comment->update($validated);

        return response()->json($comment->load('user'));
    }

    public function destroy(Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);
        $comment->delete();
        return response()->json(['success' => true]);
    }
}
