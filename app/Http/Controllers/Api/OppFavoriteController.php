<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OppFavoriteController extends Controller
{
    public function index(): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $favorites = OppFavorite::where('user_id', auth()->id())
            ->with('favorable')
            ->orderBy('position')
            ->get();

        return response()->json(['favorites' => $favorites]);
    }

    public function toggle(Request $request): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'favorable_type' => 'required|string',
            'favorable_id'   => 'required|integer',
        ]);

        $existing = OppFavorite::where('user_id', auth()->id())
            ->where('favorable_type', $validated['favorable_type'])
            ->where('favorable_id', $validated['favorable_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorited = false;
        } else {
            $maxPosition = OppFavorite::where('user_id', auth()->id())->max('position') ?? 0;

            OppFavorite::create([
                'user_id'        => auth()->id(),
                'favorable_type' => $validated['favorable_type'],
                'favorable_id'   => $validated['favorable_id'],
                'position'       => $maxPosition + 1,
                'created_at'     => now(),
            ]);

            $isFavorited = true;
        }

        return response()->json(['is_favorited' => $isFavorited]);
    }
}
