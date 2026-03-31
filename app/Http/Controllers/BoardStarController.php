<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardStar;
use Illuminate\Http\Request;

class BoardStarController extends Controller
{
    public function toggle(Board $board)
    {
        $star = BoardStar::where('board_id', $board->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($star) {
            $star->delete();
            $starred = false;
        } else {
            BoardStar::create(['board_id' => $board->id, 'user_id' => auth()->id()]);
            $starred = true;
        }

        return response()->json(['starred' => $starred]);
    }
}
