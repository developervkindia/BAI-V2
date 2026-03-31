<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return view('search.results', ['query' => $query, 'cards' => collect(), 'boards' => collect()]);
        }

        $user = $request->user();

        // Get accessible board IDs
        $boardIds = Board::where(function ($q) use ($user) {
            $q->where('visibility', 'public')
                ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
                ->orWhereHas('workspace', fn($q) => $q->where('owner_id', $user->id)
                    ->orWhereHas('members', fn($q2) => $q2->where('user_id', $user->id)));
        })->where('is_archived', false)->pluck('id');

        $cards = $this->buildCardQuery($query, $boardIds)
            ->with(['boardList', 'board', 'labels', 'members'])
            ->limit(50)
            ->get();

        $boards = Board::whereIn('id', $boardIds)
            ->where('name', 'like', "%{$query}%")
            ->with('workspace')
            ->limit(10)
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['cards' => $cards, 'boards' => $boards]);
        }

        return view('search.results', [
            'query' => $query,
            'cards' => $cards,
            'boards' => $boards,
            'workspaces' => $user->allWorkspaces(),
        ]);
    }

    public function boardSearch(Request $request, Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);

        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json(['cards' => []]);
        }

        $cards = $this->buildCardQuery($query, collect([$board->id]))
            ->with(['boardList', 'labels', 'members'])
            ->limit(50)
            ->get();

        return response()->json(['cards' => $cards]);
    }

    private function buildCardQuery(string $rawQuery, $boardIds)
    {
        $query = Card::whereIn('board_id', $boardIds)
            ->where('is_archived', false)
            ->where('is_template', false);

        // Parse advanced operators
        $textParts = [];
        $tokens = preg_split('/\s+/', $rawQuery);

        foreach ($tokens as $token) {
            if (preg_match('/^due:(\w+)$/', $token, $m)) {
                $query = match ($m[1]) {
                    'overdue' => $query->where('due_date', '<', now())->where('due_date_complete', false),
                    'week' => $query->whereBetween('due_date', [now(), now()->addWeek()]),
                    'month' => $query->whereBetween('due_date', [now(), now()->addMonth()]),
                    'none' => $query->whereNull('due_date'),
                    'complete' => $query->where('due_date_complete', true),
                    default => $query,
                };
            } elseif (preg_match('/^member:(.+)$/', $token, $m)) {
                $name = $m[1];
                $query->whereHas('members', fn($q) => $q->where('name', 'like', "%{$name}%"));
            } elseif (preg_match('/^label:(.+)$/', $token, $m)) {
                $color = $m[1];
                $query->whereHas('labels', fn($q) => $q->where('color', $color)->orWhere('name', 'like', "%{$color}%"));
            } elseif ($token === 'is:archived') {
                $query->where('is_archived', true);
            } elseif ($token === 'has:description') {
                $query->whereNotNull('description')->where('description', '!=', '');
            } elseif ($token === 'has:attachments') {
                $query->whereHas('attachments');
            } elseif ($token === 'has:checklist') {
                $query->whereHas('checklists');
            } else {
                $textParts[] = $token;
            }
        }

        $text = implode(' ', $textParts);
        if ($text) {
            $query->where(function ($q) use ($text) {
                $q->where('title', 'like', "%{$text}%")
                    ->orWhere('description', 'like', "%{$text}%");
            });
        }

        return $query;
    }
}
