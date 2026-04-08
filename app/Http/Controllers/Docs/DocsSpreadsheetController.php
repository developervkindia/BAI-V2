<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocDocument;
use App\Services\DocsQuotaService;
use Illuminate\Http\Request;

class DocsSpreadsheetController extends Controller
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
            'type' => 'spreadsheet',
            'title' => 'Untitled spreadsheet',
            'folder_id' => $request->get('folder_id'),
            'status' => 'draft',
            'body_json' => [
                'sheets' => [
                    [
                        'name' => 'Sheet1',
                        'data' => array_fill(0, 50, array_fill(0, 26, '')),
                        'style' => [],
                        'colWidths' => [],
                        'merged' => [],
                        'frozen' => ['rows' => 0, 'cols' => 0],
                    ],
                ],
                'activeSheet' => 0,
            ],
        ]);

        return redirect()->route('docs.spreadsheets.show', $doc);
    }

    public function show(Request $request, DocDocument $document)
    {
        if (! $document->isSpreadsheet()) {
            return redirect($document->getEditorRoute());
        }

        $document->load(['owner', 'folder', 'shares.user']);

        return view('docs.spreadsheets.editor', [
            'document' => $document,
            'isOwner' => $document->isOwnedBy(auth()->user()),
            'canEdit' => $document->userCan(auth()->user(), 'edit'),
        ]);
    }
}
