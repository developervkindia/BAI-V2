<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Label;
use App\Models\Workspace;
use App\Services\BoardTemplateService;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function store(Request $request, Workspace $workspace)
    {
        abort_unless($workspace->hasUser(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'background_type' => 'nullable|string|in:color,gradient,image',
            'background_value' => 'nullable|string|max:500',
            'background_image' => 'nullable|image|max:5120',
            'visibility' => 'in:private,workspace,public',
            'template' => 'nullable|string|in:blank,agile-sprint,bug-tracking,product-roadmap,devops-pipeline,release-management',
        ]);

        $bgType = $validated['background_type'] ?? 'color';
        $bgValue = $validated['background_value'] ?? '#1a1a1a';

        // Handle image upload
        if ($request->hasFile('background_image')) {
            $bgType = 'image';
            $bgValue = $request->file('background_image')->store('boards/backgrounds', 'public');
        }

        $board = Board::create([
            'workspace_id' => $workspace->id,
            'name' => $validated['name'],
            'background_type' => $bgType,
            'background_value' => $bgValue,
            'visibility' => $validated['visibility'] ?? 'workspace',
            'created_by' => auth()->id(),
        ]);

        $board->members()->attach(auth()->id(), ['role' => 'admin']);

        // Create default labels
        foreach (Label::defaultColors() as $color => $hex) {
            Label::create(['board_id' => $board->id, 'color' => $color]);
        }

        // Apply template if selected
        $template = $validated['template'] ?? 'blank';
        if ($template !== 'blank') {
            BoardTemplateService::applyTemplate($board, $template);
        }

        return redirect()->route('boards.show', $board);
    }

    public function show(Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);

        $board->load([
            'lists' => fn($q) => $q->where('is_archived', false)->orderBy('position'),
            'lists.cards' => fn($q) => $q->where('is_archived', false)->where('is_template', false)->orderBy('position'),
            'lists.cards.labels',
            'lists.cards.members',
            'lists.cards.checklists.items',
            'members',
            'labels',
            'workspace',
        ]);

        // Track recent board visit
        $board->whereHas('workspace', fn($q) => $q);
        auth()->user()->starredBoards; // preload for star check

        \DB::table('recent_boards')->updateOrInsert(
            ['board_id' => $board->id, 'user_id' => auth()->id()],
            ['last_visited_at' => now()]
        );

        return view('boards.show', [
            'board' => $board,
        ]);
    }

    public function update(Request $request, Board $board)
    {
        abort_unless($board->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'background_type' => 'sometimes|in:color,gradient,image',
            'background_value' => 'sometimes|string|max:500',
            'visibility' => 'sometimes|in:private,workspace,public',
        ]);

        $board->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'board' => $board]);
        }

        return back()->with('success', 'Board updated.');
    }

    public function archive(Board $board)
    {
        abort_unless($board->isAdmin(auth()->user()), 403);
        $board->update(['is_archived' => true, 'closed_at' => now()]);
        return redirect()->route('dashboard')->with('success', 'Board archived.');
    }

    public function restore(Board $board)
    {
        abort_unless($board->isAdmin(auth()->user()), 403);
        $board->update(['is_archived' => false, 'closed_at' => null]);
        return redirect()->route('boards.show', $board);
    }

    public function destroy(Board $board)
    {
        abort_unless($board->isAdmin(auth()->user()), 403);
        $workspaceSlug = $board->workspace->slug;
        $board->lists()->each(fn($list) => $list->cards()->forceDelete());
        $board->lists()->forceDelete();
        $board->delete();

        return redirect()->route('workspaces.show', $workspaceSlug)->with('success', 'Board deleted.');
    }

    public function archivedItems(Board $board)
    {
        abort_unless($board->canAccess(auth()->user()), 403);

        return response()->json([
            'lists' => $board->lists()->where('is_archived', true)->get(),
            'cards' => $board->cards()->where('is_archived', true)->with('boardList')->get(),
        ]);
    }
}
