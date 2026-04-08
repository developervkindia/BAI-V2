<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocDocument;
use App\Models\DocFolder;
use Illuminate\Http\Request;

class DocsFolderController extends Controller
{
    public function show(Request $request, DocFolder $folder)
    {
        $org = auth()->user()->currentOrganization();

        $subfolders = DocFolder::where('organization_id', $org->id)
            ->where('parent_id', $folder->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $documents = DocDocument::where('organization_id', $org->id)
            ->where('folder_id', $folder->id)
            ->accessibleBy(auth()->user())
            ->with(['owner'])
            ->orderByDesc('updated_at')
            ->paginate(24);

        $breadcrumbs = $this->buildBreadcrumbs($folder);

        return view('docs.folders.show', compact('folder', 'subfolders', 'documents', 'breadcrumbs'));
    }

    private function buildBreadcrumbs(DocFolder $folder): array
    {
        $crumbs = [];
        $current = $folder;
        while ($current) {
            array_unshift($crumbs, $current);
            $current = $current->parent;
        }

        return $crumbs;
    }
}
