<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use App\Models\EmployeeProfile;
use App\Models\HrAnnouncement;
use App\Models\OppProject;
use App\Models\OppTask;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);

        $q    = $request->input('q');
        $user = $request->user();
        $org  = $user->currentOrganization();

        if (!$org) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Boards
        $boards = Board::whereHas('workspace', fn ($w) => $w->where('organization_id', $org->id))
            ->where('name', 'like', "%{$q}%")
            ->where('is_archived', false)
            ->limit(5)
            ->get(['id', 'name', 'slug']);
        foreach ($boards as $board) {
            $results[] = [
                'type'  => 'board',
                'label' => $board->name,
                'url'   => route('boards.show', $board),
            ];
        }

        // Cards
        $cards = Card::whereHas('boardList.board.workspace', fn ($w) => $w->where('organization_id', $org->id))
            ->where('title', 'like', "%{$q}%")
            ->where('is_archived', false)
            ->limit(5)
            ->get(['id', 'title']);
        foreach ($cards as $card) {
            $results[] = [
                'type'  => 'card',
                'label' => $card->title,
                'url'   => '#card-' . $card->id,
            ];
        }

        // Projects
        $projects = Project::where('organization_id', $org->id)
            ->where('name', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'name', 'slug']);
        foreach ($projects as $project) {
            $results[] = [
                'type'  => 'project',
                'label' => $project->name,
                'url'   => route('projects.show', $project),
            ];
        }

        // Project Tasks
        $tasks = ProjectTask::whereHas('project', fn ($p) => $p->where('organization_id', $org->id))
            ->where('title', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'title']);
        foreach ($tasks as $task) {
            $results[] = [
                'type'  => 'project_task',
                'label' => $task->title,
                'url'   => '#task-' . $task->id,
            ];
        }

        // Opportunity projects and tasks
        $oppProjects = OppProject::where('organization_id', $org->id)
            ->where('name', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'name', 'slug']);
        foreach ($oppProjects as $op) {
            $results[] = [
                'type'  => 'opp_project',
                'label' => $op->name,
                'url'   => route('opportunity.projects.show', $op),
            ];
        }

        $oppTasks = OppTask::whereHas('section.project', fn ($p) => $p->where('organization_id', $org->id))
            ->where('name', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'name']);
        foreach ($oppTasks as $ot) {
            $results[] = [
                'type'  => 'opp_task',
                'label' => $ot->name,
                'url'   => '#opp-task-' . $ot->id,
            ];
        }

        // People
        $people = EmployeeProfile::where('organization_id', $org->id)
            ->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
            ->with('user:id,name')
            ->limit(5)
            ->get(['id', 'user_id']);
        foreach ($people as $person) {
            $results[] = [
                'type'  => 'person',
                'label' => $person->user->name ?? 'Unknown',
                'url'   => route('hr.people.show', $person),
            ];
        }

        // Announcements
        $announcements = HrAnnouncement::where('organization_id', $org->id)
            ->where('title', 'like', "%{$q}%")
            ->where('status', 'published')
            ->limit(3)
            ->get(['id', 'title']);
        foreach ($announcements as $ann) {
            $results[] = [
                'type'  => 'announcement',
                'label' => $ann->title,
                'url'   => route('hr.announcements.show', $ann),
            ];
        }

        return response()->json([
            'query'   => $q,
            'results' => array_slice($results, 0, 25),
            'count'   => count($results),
        ]);
    }
}
