<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocDocument;
use App\Models\DocFolder;
use App\Models\DocStar;
use App\Services\DocsAutoSaveService;
use App\Services\DocsQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocDocumentApiController extends Controller
{
    public function __construct(
        private DocsAutoSaveService $autoSaveService,
        private DocsQuotaService $quota,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:document,spreadsheet,form,presentation',
            'title' => 'sometimes|string|max:255',
            'folder_id' => 'nullable|integer|exists:doc_folders,id',
        ]);

        $org = auth()->user()->currentOrganization();

        if (! $this->quota->canCreateDocument($org)) {
            return response()->json(['success' => false, 'message' => 'Document limit reached.'], 422);
        }

        $doc = DocDocument::create([
            'organization_id' => $org->id,
            'owner_id' => auth()->id(),
            'type' => $request->input('type'),
            'title' => $request->input('title', 'Untitled'),
            'folder_id' => $request->input('folder_id'),
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $doc->id,
                'slug' => $doc->slug,
                'url' => $doc->getEditorRoute(),
            ],
        ], 201);
    }

    public function autoSave(Request $request, DocDocument $document): JsonResponse
    {
        if (! $document->userCan(auth()->user(), 'edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $clientVersion = $request->input('version');
        if ($clientVersion && (int) $clientVersion !== $document->version) {
            return response()->json([
                'success' => false,
                'error' => 'conflict',
                'server_version' => $document->version,
                'message' => 'Document was modified by another user.',
            ], 409);
        }

        $result = $this->autoSaveService->save($document, $request->only([
            'title', 'body_html', 'body_json', 'settings',
        ]), auth()->user());

        return response()->json([
            'success' => true,
            ...$result,
        ]);
    }

    public function destroy(Request $request, DocDocument $document): JsonResponse
    {
        if (! $document->isOwnedBy(auth()->user())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $document->delete();

        return response()->json(['success' => true, 'message' => 'Document moved to trash.']);
    }

    public function restore(Request $request, int $id): JsonResponse
    {
        $org = auth()->user()->currentOrganization();
        $document = DocDocument::onlyTrashed()
            ->where('id', $id)
            ->where('organization_id', $org->id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $document->restore();

        return response()->json(['success' => true, 'message' => 'Document restored.']);
    }

    public function duplicate(Request $request, DocDocument $document): JsonResponse
    {
        $org = auth()->user()->currentOrganization();

        if (! $this->quota->canCreateDocument($org)) {
            return response()->json(['success' => false, 'message' => 'Document limit reached.'], 422);
        }

        $copy = $document->replicate(['slug', 'sharing_token', 'version']);
        $copy->title = $document->title . ' (Copy)';
        $copy->owner_id = auth()->id();
        $copy->version = 1;
        $copy->status = 'draft';
        $copy->sharing_mode = 'private';
        $copy->last_edited_by = auth()->id();
        $copy->last_edited_at = now();
        $copy->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $copy->id,
                'slug' => $copy->slug,
                'url' => $copy->getEditorRoute(),
            ],
        ]);
    }

    public function move(Request $request, DocDocument $document): JsonResponse
    {
        $request->validate([
            'folder_id' => 'nullable|integer|exists:doc_folders,id',
        ]);

        if (! $document->isOwnedBy(auth()->user())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $document->update(['folder_id' => $request->input('folder_id')]);

        return response()->json(['success' => true, 'message' => 'Document moved.']);
    }

    public function toggleStar(Request $request, DocDocument $document): JsonResponse
    {
        $star = DocStar::where('document_id', $document->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($star) {
            $star->delete();
            $starred = false;
        } else {
            DocStar::create([
                'document_id' => $document->id,
                'user_id' => auth()->id(),
            ]);
            $starred = true;
        }

        return response()->json(['success' => true, 'starred' => $starred]);
    }

    public function forceDestroy(Request $request, int $id): JsonResponse
    {
        $org = auth()->user()->currentOrganization();
        $document = DocDocument::onlyTrashed()
            ->where('id', $id)
            ->where('organization_id', $org->id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        // Delete attachments from disk
        foreach ($document->attachments as $attachment) {
            $attachment->deleteFile();
            $attachment->delete();
        }

        $document->forceDelete();

        return response()->json(['success' => true, 'message' => 'Document permanently deleted.']);
    }
}
