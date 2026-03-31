<?php

namespace App\Http\Controllers\Api;

use App\Events\BoardEvent;
use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardMessage;
use Illuminate\Http\Request;

class BoardChatController extends Controller
{
    public function index(Request $request, Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);

        $query = $board->hasMany(BoardMessage::class)->with('user:id,name,avatar_path')->orderByDesc('created_at');

        // Cursor pagination: load older messages before a given ID
        if ($request->has('before')) {
            $query->where('id', '<', $request->input('before'));
        }

        $messages = $query->limit(50)->get()->reverse()->values();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'body' => $m->body,
                'user' => [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                    'avatar_url' => $m->user->avatar_url,
                ],
                'created_at' => $m->created_at->toISOString(),
            ]),
            'has_more' => $query->count() > 50,
        ]);
    }

    public function store(Request $request, Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $message = BoardMessage::create([
            'board_id' => $board->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $message->load('user:id,name,avatar_path');

        $payload = [
            'id' => $message->id,
            'user_id' => $message->user_id,
            'body' => $message->body,
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'avatar_url' => $message->user->avatar_url,
            ],
            'created_at' => $message->created_at->toISOString(),
        ];

        broadcast(new BoardEvent($board->id, 'chat.message', $payload))->toOthers();

        return response()->json($payload, 201);
    }

    public function destroy(BoardMessage $message)
    {
        $user = auth()->user();
        $board = $message->board;

        // Own message or board admin can delete
        abort_unless($message->user_id === $user->id || $board->isAdmin($user), 403);

        $messageId = $message->id;
        $boardId = $message->board_id;
        $message->delete();

        broadcast(new BoardEvent($boardId, 'chat.message.deleted', ['id' => $messageId]))->toOthers();

        return response()->json(['success' => true]);
    }
}
