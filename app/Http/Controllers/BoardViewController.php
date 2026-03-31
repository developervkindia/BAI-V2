<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Support\Facades\DB;

class BoardViewController extends Controller
{
    private function loadBoardData(Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);

        $board->load([
            'lists' => fn($q) => $q->where('is_archived', false)->orderBy('position'),
            'lists.cards' => fn($q) => $q->where('is_archived', false)->where('is_template', false)->orderBy('position'),
            'lists.cards.labels',
            'lists.cards.members',
            'lists.cards.checklists.items',
            'labels',
            'members',
            'workspace',
            'customFields',
        ]);

        // Track recent visit
        DB::table('recent_boards')->updateOrInsert(
            ['board_id' => $board->id, 'user_id' => auth()->id()],
            ['last_visited_at' => now()]
        );

        return $board;
    }

    public function calendar(Board $board)
    {
        $board = $this->loadBoardData($board);
        return view('boards.calendar', compact('board'));
    }

    public function timeline(Board $board)
    {
        $board = $this->loadBoardData($board);
        return view('boards.timeline', compact('board'));
    }

    public function table(Board $board)
    {
        $board = $this->loadBoardData($board);
        return view('boards.table', compact('board'));
    }

    public function boardDashboard(Board $board)
    {
        $board = $this->loadBoardData($board);
        return view('boards.dashboard-view', compact('board'));
    }
}
