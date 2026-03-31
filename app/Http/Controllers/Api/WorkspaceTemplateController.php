<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Workspace;
use App\Models\WorkspaceTemplate;
use App\Services\PositionService;
use Illuminate\Http\Request;

class WorkspaceTemplateController extends Controller
{
    public function index()
    {
        $templates = WorkspaceTemplate::where('created_by', auth()->id())->get();
        return response()->json($templates);
    }

    public function store(Request $request, Workspace $workspace)
    {
        abort_unless($workspace->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Capture workspace structure
        $structure = [
            'boards' => $workspace->boards()->where('is_archived', false)->get()->map(function ($board) {
                return [
                    'name' => $board->name,
                    'description' => $board->description,
                    'background_type' => $board->background_type,
                    'background_value' => $board->background_value,
                    'visibility' => $board->visibility,
                    'lists' => $board->lists()->where('is_archived', false)->orderBy('position')->get()->map(function ($list) {
                        return ['name' => $list->name];
                    })->toArray(),
                ];
            })->toArray(),
        ];

        $template = WorkspaceTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'structure' => $structure,
            'created_by' => auth()->id(),
        ]);

        return response()->json($template, 201);
    }

    public function createFromTemplate(Request $request, WorkspaceTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $workspace = Workspace::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'owner_id' => auth()->id(),
        ]);

        $workspace->members()->attach(auth()->id(), ['role' => 'admin']);

        // Apply structure
        if (!empty($template->structure['boards'])) {
            foreach ($template->structure['boards'] as $boardData) {
                $board = $workspace->boards()->create([
                    'name' => $boardData['name'],
                    'description' => $boardData['description'] ?? null,
                    'background_type' => $boardData['background_type'] ?? 'gradient',
                    'background_value' => $boardData['background_value'] ?? 'linear-gradient(135deg, #7c3aed, #d946ef)',
                    'visibility' => $boardData['visibility'] ?? 'workspace',
                    'created_by' => auth()->id(),
                ]);

                $board->members()->attach(auth()->id(), ['role' => 'admin']);

                // Create default labels
                foreach (Label::defaultColors() as $color => $hex) {
                    $board->labels()->create(['color' => $color]);
                }

                // Create lists
                if (!empty($boardData['lists'])) {
                    foreach ($boardData['lists'] as $index => $listData) {
                        $board->lists()->create([
                            'name' => $listData['name'],
                            'position' => ($index + 1) * PositionService::GAP,
                        ]);
                    }
                }
            }
        }

        return response()->json($workspace->load('boards.lists'), 201);
    }

    public function destroy(WorkspaceTemplate $template)
    {
        abort_unless($template->created_by === auth()->id(), 403);
        $template->delete();
        return response()->json(['success' => true]);
    }
}
