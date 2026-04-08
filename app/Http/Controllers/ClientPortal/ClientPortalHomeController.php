<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\ClientDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientPortalHomeController extends Controller
{
    public function dashboard()
    {
        $user = Auth::guard('client_portal')->user();
        $client = $user->client()->with(['hiredProject', 'organization'])->firstOrFail();

        $documents = $client->documents()
            ->where('visibility', 'portal')
            ->latest()
            ->get();

        return view('client-portal.home', compact('user', 'client', 'documents'));
    }

    public function downloadDocument(ClientDocument $document)
    {
        $user = Auth::guard('client_portal')->user();

        abort_unless($document->client_id === $user->client_id, 403);
        abort_unless($document->visibility === 'portal', 403);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }
}
