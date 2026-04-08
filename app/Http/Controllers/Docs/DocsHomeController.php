<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocDocument;
use App\Models\DocFolder;
use Illuminate\Http\Request;

class DocsHomeController extends Controller
{
    public function index(Request $request)
    {
        $org = auth()->user()->currentOrganization();
        $type = $request->get('type');
        $view = $request->get('view', 'grid');
        $sort = $request->get('sort', 'updated_at');

        $query = DocDocument::where('organization_id', $org->id)
            ->accessibleBy(auth()->user())
            ->whereNull('deleted_at');

        if ($type && in_array($type, ['document', 'spreadsheet', 'form', 'presentation'])) {
            $query->ofType($type);
        }

        $documents = $query->with(['owner', 'folder'])
            ->orderByDesc($sort === 'title' ? 'title' : 'updated_at')
            ->paginate(24);

        $folders = DocFolder::where('organization_id', $org->id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => DocDocument::where('organization_id', $org->id)->count(),
            'documents' => DocDocument::where('organization_id', $org->id)->ofType('document')->count(),
            'spreadsheets' => DocDocument::where('organization_id', $org->id)->ofType('spreadsheet')->count(),
            'forms' => DocDocument::where('organization_id', $org->id)->ofType('form')->count(),
            'presentations' => DocDocument::where('organization_id', $org->id)->ofType('presentation')->count(),
        ];

        return view('docs.index', compact('documents', 'folders', 'stats', 'type', 'view', 'sort'));
    }

    public function starred(Request $request)
    {
        $org = auth()->user()->currentOrganization();
        $user = auth()->user();

        $documents = DocDocument::where('organization_id', $org->id)
            ->whereHas('stars', fn ($q) => $q->where('user_id', $user->id))
            ->with(['owner', 'folder'])
            ->orderByDesc('updated_at')
            ->paginate(24);

        return view('docs.starred', compact('documents'));
    }

    public function sharedWithMe(Request $request)
    {
        $user = auth()->user();
        $org = $user->currentOrganization();

        $documents = DocDocument::where('organization_id', $org->id)
            ->where('owner_id', '!=', $user->id)
            ->whereHas('shares', fn ($q) => $q->where('user_id', $user->id))
            ->with(['owner', 'folder'])
            ->orderByDesc('updated_at')
            ->paginate(24);

        return view('docs.shared', compact('documents'));
    }

    public function trash(Request $request)
    {
        $org = auth()->user()->currentOrganization();

        $documents = DocDocument::where('organization_id', $org->id)
            ->where('owner_id', auth()->id())
            ->onlyTrashed()
            ->with(['owner', 'folder'])
            ->orderByDesc('deleted_at')
            ->paginate(24);

        return view('docs.trash', compact('documents'));
    }
}
