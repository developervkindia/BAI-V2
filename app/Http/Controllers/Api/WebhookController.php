<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $org = $user->currentOrganization();
        abort_unless($org, 403);

        $webhooks = Webhook::where('organization_id', $org->id)
            ->latest()
            ->get();

        return response()->json($webhooks);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $org = $user->currentOrganization();
        abort_unless($org, 403);

        $validated = $request->validate([
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $webhook = Webhook::create(array_merge($validated, [
            'organization_id' => $org->id,
            'created_by' => $user->id,
            'is_active' => true,
        ]));

        return response()->json($webhook, 201);
    }

    public function update(Request $request, Webhook $webhook)
    {
        abort_unless($webhook->organization_id === auth()->user()->currentOrganization()?->id, 403);

        $validated = $request->validate([
            'url' => 'sometimes|required|url|max:500',
            'events' => 'sometimes|required|array|min:1',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $webhook->update($validated);

        return response()->json($webhook);
    }

    public function destroy(Webhook $webhook)
    {
        abort_unless($webhook->organization_id === auth()->user()->currentOrganization()?->id, 403);

        $webhook->delete();

        return response()->json(['message' => 'Webhook deleted.']);
    }

    public function logs(Webhook $webhook)
    {
        abort_unless($webhook->organization_id === auth()->user()->currentOrganization()?->id, 403);

        $logs = $webhook->logs()
            ->latest()
            ->limit(50)
            ->get();

        return response()->json($logs);
    }

    public function test(Webhook $webhook)
    {
        abort_unless($webhook->organization_id === auth()->user()->currentOrganization()?->id, 403);

        $payload = [
            'event' => 'ping',
            'webhook_id' => $webhook->id,
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            $headers = ['Content-Type' => 'application/json'];

            if ($webhook->secret) {
                $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);
                $headers['X-Webhook-Signature'] = $signature;
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($webhook->url, $payload);

            return response()->json([
                'success' => $response->successful(),
                'status_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
