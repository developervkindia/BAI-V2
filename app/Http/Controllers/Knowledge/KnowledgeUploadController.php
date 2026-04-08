<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeAttachment;
use App\Services\KnowledgeQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KnowledgeUploadController extends Controller
{
    public function uploadImage(Request $request): JsonResponse
    {
        $this->authorize('create', KnowledgeArticle::class);

        $org = $request->user()->currentOrganization();
        abort_unless($org, 404);

        $quota = app(KnowledgeQuotaService::class);
        abort_unless($quota->attachmentsEnabled($org), 403);

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:jpeg,jpg,png,gif,webp'],
        ]);

        $file = $request->file('file');
        $bytes = $file->getSize() ?: 0;
        abort_unless($quota->canUploadBytes($org, $bytes), 403, 'Storage limit reached for your plan.');

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = 'knowledge/'.$org->id.'/images/'.Str::uuid().'.'.$ext;
        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        $attachment = KnowledgeAttachment::create([
            'organization_id' => $org->id,
            'knowledge_article_id' => null,
            'uploaded_by' => $request->user()->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?: 'image/jpeg',
            'size' => $bytes,
        ]);

        $url = route('knowledge.files.show', $attachment);

        return response()->json(['location' => $url]);
    }

    public function uploadAttachment(Request $request): JsonResponse
    {
        $this->authorize('create', KnowledgeArticle::class);

        $org = $request->user()->currentOrganization();
        abort_unless($org, 404);

        $quota = app(KnowledgeQuotaService::class);
        abort_unless($quota->attachmentsEnabled($org), 403);

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('file');
        $bytes = $file->getSize() ?: 0;
        abort_unless($quota->canUploadBytes($org, $bytes), 403, 'Storage limit reached for your plan.');

        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $path = 'knowledge/'.$org->id.'/attachments/'.Str::uuid().'.'.$ext;
        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        $attachment = KnowledgeAttachment::create([
            'organization_id' => $org->id,
            'knowledge_article_id' => null,
            'uploaded_by' => $request->user()->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $bytes,
        ]);

        return response()->json([
            'id' => $attachment->id,
            'name' => $attachment->original_name,
            'download_url' => route('knowledge.files.download', $attachment),
        ]);
    }

    public function show(Request $request, KnowledgeAttachment $attachment): StreamedResponse|Response
    {
        $this->authorizeAttachment($request, $attachment);

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime,
        ]);
    }

    public function download(Request $request, KnowledgeAttachment $attachment): StreamedResponse|Response
    {
        return $this->show($request, $attachment);
    }

    private function authorizeAttachment(Request $request, KnowledgeAttachment $attachment): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->is_super_admin) {
            return;
        }

        abort_unless($user->currentOrganization()?->id === $attachment->organization_id, 403);

        if ($attachment->knowledge_article_id === null) {
            abort_unless($attachment->uploaded_by === $user->id, 403);

            return;
        }

        $article = $attachment->article;
        abort_unless($article, 403);
        $this->authorize('view', $article);
    }
}
