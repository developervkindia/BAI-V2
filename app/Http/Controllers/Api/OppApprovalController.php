<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OppApprovalController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'task_id' => 'required|exists:opp_tasks,id',
        ]);

        $approval = OppApproval::create([
            'task_id'      => $validated['task_id'],
            'status'       => 'pending',
            'requested_by' => auth()->id(),
        ]);

        return response()->json(['approval' => $approval->load(['task', 'requester'])], 201);
    }

    public function approve(OppApproval $approval): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $approval->update([
            'status'     => 'approved',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return response()->json(['approval' => $approval->fresh(['task', 'requester', 'decider'])]);
    }

    public function reject(Request $request, OppApproval $approval): JsonResponse
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $approval->update([
            'status'     => 'rejected',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
            'comment'    => $validated['comment'],
        ]);

        return response()->json(['approval' => $approval->fresh(['task', 'requester', 'decider'])]);
    }
}
