<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocDocument;
use App\Services\DocsQuotaService;
use Illuminate\Http\Request;

class DocsDocumentController extends Controller
{
    public function __construct(private DocsQuotaService $quota) {}

    public function create(Request $request)
    {
        $org = auth()->user()->currentOrganization();

        if (! $this->quota->canCreateDocument($org)) {
            return redirect()->route('docs.index')->with('error', 'Document limit reached for your plan.');
        }

        $doc = DocDocument::create([
            'organization_id' => $org->id,
            'owner_id' => auth()->id(),
            'type' => 'document',
            'title' => 'Untitled document',
            'folder_id' => $request->get('folder_id'),
            'status' => 'draft',
        ]);

        return redirect()->route('docs.documents.show', $doc);
    }

    public function show(Request $request, DocDocument $document)
    {
        if (! $document->isDocument()) {
            return redirect($document->getEditorRoute());
        }

        $document->load(['owner', 'folder', 'shares.user']);

        return view('docs.documents.editor', [
            'document' => $document,
            'isOwner' => $document->isOwnedBy(auth()->user()),
            'canEdit' => $document->userCan(auth()->user(), 'edit'),
        ]);
    }
}
