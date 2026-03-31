<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppSection;
use Illuminate\Http\Request;

class OppSectionController extends Controller
{
    /**
     * Create a new section in a project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:opp_projects,id',
        ]);

        $maxPosition = OppSection::where('project_id', $validated['project_id'])
            ->max('position') ?? 0;

        $section = OppSection::create(array_merge($validated, [
            'position' => $maxPosition + 1000,
        ]));

        return response()->json(['section' => $section], 201);
    }

    /**
     * Update a section name.
     */
    public function update(Request $request, OppSection $section)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $section->update($validated);

        return response()->json(['section' => $section]);
    }

    /**
     * Delete a section and reassign its tasks.
     */
    public function destroy(OppSection $section)
    {
        $projectId = $section->project_id;

        // Find first remaining section to move tasks into
        $fallbackSection = OppSection::where('project_id', $projectId)
            ->where('id', '!=', $section->id)
            ->orderBy('position')
            ->first();

        // Move tasks to fallback section or set to null
        $section->tasks()->update([
            'section_id' => $fallbackSection?->id,
        ]);

        $section->delete();

        return response()->json(['message' => 'Section deleted.']);
    }

    /**
     * Batch reorder sections by position.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:opp_sections,id',
            'items.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            OppSection::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json(['message' => 'Sections reordered.']);
    }
}
