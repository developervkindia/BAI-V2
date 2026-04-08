<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocDocument;
use App\Models\DocFormResponse;
use App\Services\DocsQuotaService;
use Illuminate\Http\Request;

class DocsFormController extends Controller
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
            'type' => 'form',
            'title' => 'Untitled form',
            'folder_id' => $request->get('folder_id'),
            'status' => 'draft',
            'body_json' => [
                'questions' => [],
                'settings' => [
                    'collect_email' => false,
                    'limit_responses' => null,
                    'shuffle_questions' => false,
                    'confirmation_message' => 'Your response has been recorded.',
                    'allow_edit_after_submit' => false,
                ],
            ],
        ]);

        return redirect()->route('docs.forms.show', $doc);
    }

    public function show(Request $request, DocDocument $document)
    {
        if (! $document->isForm()) {
            return redirect($document->getEditorRoute());
        }

        $document->load(['owner', 'folder', 'shares.user']);
        $responseCount = $document->formResponses()->count();

        return view('docs.forms.editor', [
            'document' => $document,
            'isOwner' => $document->isOwnedBy(auth()->user()),
            'canEdit' => $document->userCan(auth()->user(), 'edit'),
            'responseCount' => $responseCount,
        ]);
    }

    public function responses(Request $request, DocDocument $document)
    {
        if (! $document->isForm()) {
            return redirect()->route('docs.index');
        }

        $responses = $document->formResponses()
            ->orderByDesc('submitted_at')
            ->paginate(50);

        return view('docs.forms.responses', [
            'document' => $document,
            'responses' => $responses,
        ]);
    }

    public function showPublic(string $slug)
    {
        $document = DocDocument::where('slug', $slug)
            ->where('type', 'form')
            ->where('status', 'published')
            ->firstOrFail();

        return view('docs.forms.public', ['document' => $document]);
    }

    public function submitPublic(Request $request, string $slug)
    {
        $document = DocDocument::where('slug', $slug)
            ->where('type', 'form')
            ->where('status', 'published')
            ->firstOrFail();

        $org = $document->organization;

        if (! $this->quota->canCollectFormResponse($org, $document)) {
            return back()->with('error', 'This form has reached its response limit.');
        }

        $formData = $document->body_json ?? [];
        $settings = $formData['settings'] ?? $document->settings ?? [];
        $validated = [];

        if (! empty($settings['collect_email'])) {
            $request->validate(['respondent_email' => 'required|email']);
            $validated['respondent_email'] = $request->input('respondent_email');
        }

        DocFormResponse::create([
            'document_id' => $document->id,
            'respondent_name' => $request->input('respondent_name'),
            'respondent_email' => $validated['respondent_email'] ?? $request->input('respondent_email'),
            'data' => $request->input('responses', []),
            'ip_address' => $request->ip(),
            'submitted_at' => now(),
        ]);

        $confirmationMessage = $settings['confirmation_message'] ?? 'Your response has been recorded.';

        return view('docs.forms.public-thanks', [
            'document' => $document,
            'message' => $confirmationMessage,
        ]);
    }
}
