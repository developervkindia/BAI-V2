<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes (Token-Authenticated)
|--------------------------------------------------------------------------
|
| These routes are accessible via Sanctum personal access tokens for
| mobile apps, third-party integrations, and external API consumers.
| All routes are prefixed with /api/v1/ via the bootstrap config.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Current user
    Route::get('/me', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_path' => $user->avatar_path,
            'organizations' => $user->allOrganizations()->map(fn ($org) => [
                'id' => $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'role' => $org->pivot->role ?? null,
            ]),
        ]);
    });

    // Token management
    Route::post('/tokens', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'array',
            'abilities.*' => 'string',
        ]);

        $token = $request->user()->createToken(
            $request->name,
            $request->abilities ?? ['*']
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'name' => $token->accessToken->name,
            'abilities' => $token->accessToken->abilities,
        ], 201);
    });

    Route::get('/tokens', function (Request $request) {
        return response()->json([
            'tokens' => $request->user()->tokens->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'abilities' => $t->abilities,
                'last_used_at' => $t->last_used_at,
                'created_at' => $t->created_at,
            ]),
        ]);
    });

    Route::delete('/tokens/{tokenId}', function (Request $request, $tokenId) {
        $request->user()->tokens()->where('id', $tokenId)->delete();
        return response()->json(['success' => true]);
    });

    // Products accessible to user
    Route::get('/products', function (Request $request) {
        $products = app(\App\Services\ProductAccessService::class)
            ->getAccessibleProducts($request->user());

        return response()->json(['products' => $products]);
    });

    // Organization context
    Route::get('/organizations', function (Request $request) {
        return response()->json([
            'organizations' => $request->user()->allOrganizations()->map(fn ($org) => [
                'id' => $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'role' => $org->pivot->role ?? null,
                'is_active' => $org->is_active,
            ]),
        ]);
    });

    // Global search
    Route::middleware('throttle:global-search')
        ->get('/search', [\App\Http\Controllers\Api\GlobalSearchController::class, 'search']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);

});
