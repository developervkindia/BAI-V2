<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocAttachment;
use App\Services\DocsQuotaService;
use Illuminate\Http\Request;

class DocsUploadController extends Controller
{
    public function __construct(private DocsQuotaService $quota) {}

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240', // 10MB max
        ]);

        $org = auth()->user()->currentOrganization();
        $file = $request->file('file');

        if (! $this->quota->canUploadBytes($org, $file->getSize())) {
            return response()->json(['error' => 'Storage limit exceeded.'], 422);
        }

        $path = $file->store("docs/{$org->id}/images", 'public');

        $attachment = DocAttachment::create([
            'organization_id' => $org->id,
            'document_id' => $request->input('document_id'),
            'uploaded_by' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        // TinyMCE expects { location: url }
        return response()->json([
            'location' => asset("storage/{$path}"),
        ]);
    }

    public function show(DocAttachment $attachment)
    {
        return response()->file(
            storage_path("app/public/{$attachment->path}")
        );
    }

    public function download(DocAttachment $attachment)
    {
        return response()->download(
            storage_path("app/public/{$attachment->path}"),
            $attachment->original_name
        );
    }
}
