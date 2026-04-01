<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrAnnouncement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrAnnouncementApiController extends Controller
{
    /**
     * Create a new announcement.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string|in:general,policy,event,holiday,urgent',
            'target_departments' => 'nullable|array',
            'target_departments.*' => 'integer|exists:hr_departments,id',
            'is_pinned' => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $announcement = HrAnnouncement::create(array_merge($validated, [
            'organization_id' => $org->id,
            'created_by' => auth()->id(),
            'published_at' => $validated['published_at'] ?? Carbon::now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully.',
            'announcement' => $announcement,
        ]);
    }

    /**
     * Update an existing announcement.
     */
    public function update(Request $request, HrAnnouncement $announcement)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string|in:general,policy,event,holiday,urgent',
            'target_departments' => 'nullable|array',
            'target_departments.*' => 'integer|exists:hr_departments,id',
            'is_pinned' => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $announcement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully.',
            'announcement' => $announcement->fresh(),
        ]);
    }

    /**
     * Delete an announcement.
     */
    public function destroy(HrAnnouncement $announcement)
    {
        abort_unless(auth()->check(), 401);

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully.',
        ]);
    }

    /**
     * Toggle the pinned status of an announcement.
     */
    public function pin(HrAnnouncement $announcement)
    {
        abort_unless(auth()->check(), 401);

        $announcement->update([
            'is_pinned' => !$announcement->is_pinned,
        ]);

        return response()->json([
            'success' => true,
            'message' => $announcement->is_pinned ? 'Announcement pinned.' : 'Announcement unpinned.',
            'announcement' => $announcement->fresh(),
        ]);
    }
}
