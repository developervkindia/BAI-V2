<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppTag;
use App\Models\OppTask;
use Illuminate\Http\Request;

class OppTagController extends Controller
{
    /**
     * List all tags for the current organization.
     */
    public function index()
    {
        $org = auth()->user()->currentOrganization();

        $tags = OppTag::where('organization_id', $org->id)
            ->orderBy('name')
            ->get();

        return response()->json(['tags' => $tags]);
    }

    /**
     * Create a new tag.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|max:7',
        ]);

        $org = auth()->user()->currentOrganization();

        $tag = OppTag::create(array_merge($validated, [
            'organization_id' => $org->id,
        ]));

        return response()->json(['tag' => $tag], 201);
    }

    /**
     * Update a tag.
     */
    public function update(Request $request, OppTag $tag)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'color' => 'sometimes|required|string|max:7',
        ]);

        $tag->update($validated);

        return response()->json(['tag' => $tag]);
    }

    /**
     * Delete a tag.
     */
    public function destroy(OppTag $tag)
    {
        $tag->delete();

        return response()->json(['message' => 'Tag deleted.']);
    }

    /**
     * Toggle a tag on a task.
     */
    public function toggle(Request $request, OppTask $task)
    {
        $validated = $request->validate([
            'tag_id' => 'required|exists:opp_tags,id',
        ]);

        $task->tags()->toggle($validated['tag_id']);

        return response()->json([
            'tags' => $task->fresh()->tags,
        ]);
    }
}
