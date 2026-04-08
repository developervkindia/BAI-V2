<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocDocument;
use App\Services\DocsQuotaService;
use Illuminate\Http\Request;

class DocsPresentationController extends Controller
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
            'type' => 'presentation',
            'title' => 'Untitled presentation',
            'folder_id' => $request->get('folder_id'),
            'status' => 'draft',
            'body_json' => [
                'theme' => 'dark',
                'transition' => 'slide',
                'slides' => [
                    [
                        'id' => 's_' . uniqid(),
                        'elements' => [
                            [
                                'id' => 'e_' . uniqid(),
                                'type' => 'text',
                                'content' => 'Click to add title',
                                'x' => 10,
                                'y' => 30,
                                'width' => 80,
                                'height' => 20,
                                'style' => [
                                    'fontSize' => 44,
                                    'fontWeight' => 'bold',
                                    'color' => '#ffffff',
                                    'textAlign' => 'center',
                                ],
                            ],
                            [
                                'id' => 'e_' . uniqid(),
                                'type' => 'text',
                                'content' => 'Click to add subtitle',
                                'x' => 20,
                                'y' => 55,
                                'width' => 60,
                                'height' => 10,
                                'style' => [
                                    'fontSize' => 24,
                                    'color' => '#94a3b8',
                                    'textAlign' => 'center',
                                ],
                            ],
                        ],
                        'background' => [
                            'type' => 'solid',
                            'value' => '#1e293b',
                        ],
                        'notes' => '',
                    ],
                ],
            ],
        ]);

        return redirect()->route('docs.presentations.show', $doc);
    }

    public function show(Request $request, DocDocument $document)
    {
        if (! $document->isPresentation()) {
            return redirect($document->getEditorRoute());
        }

        $document->load(['owner', 'folder', 'shares.user']);

        return view('docs.presentations.editor', [
            'document' => $document,
            'isOwner' => $document->isOwnedBy(auth()->user()),
            'canEdit' => $document->userCan(auth()->user(), 'edit'),
        ]);
    }

    public function present(Request $request, DocDocument $document)
    {
        if (! $document->isPresentation()) {
            return redirect()->route('docs.index');
        }

        return view('docs.presentations.present', ['document' => $document]);
    }
}
