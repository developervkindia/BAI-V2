<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrAnnouncement;
use Illuminate\Http\Request;

class HrAnnouncementController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();

        $announcements = HrAnnouncement::where('organization_id', $organization->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('hr.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('hr.announcements.create');
    }

    public function show(HrAnnouncement $announcement)
    {
        return view('hr.announcements.show', compact('announcement'));
    }

    public function store(Request $request)
    {
        $organization = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['organization_id'] = $organization->id;
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['published_at'] = $validated['published_at'] ?? now();

        HrAnnouncement::create($validated);

        return redirect()->route('hr.announcements.index')->with('success', 'Announcement created successfully.');
    }
}
