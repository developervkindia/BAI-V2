<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $workspaces = $user->allWorkspaces();

        $starredBoards = $user->starredBoards()
            ->where('is_archived', false)
            ->with('workspace')
            ->get();

        $recentBoards = DB::table('recent_boards')
            ->where('user_id', $user->id)
            ->orderByDesc('last_visited_at')
            ->limit(8)
            ->pluck('board_id');

        $recentBoardModels = Board::whereIn('id', $recentBoards)
            ->where('is_archived', false)
            ->with('workspace')
            ->get()
            ->sortBy(fn($b) => array_search($b->id, $recentBoards->toArray()));

        // Boards where user is a direct member but NOT part of the workspace
        // (invited boards from other people's workspaces)
        $workspaceBoardIds = $workspaces->flatMap(fn($w) => $w->boards->pluck('id'))->toArray();

        $invitedBoards = Board::whereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->where('is_archived', false)
            ->whereNotIn('id', $workspaceBoardIds)
            ->with('workspace')
            ->get();

        return view('dashboard', [
            'workspaces' => $workspaces,
            'starredBoards' => $starredBoards,
            'recentBoards' => $recentBoardModels,
            'invitedBoards' => $invitedBoards,
        ]);
    }
}
