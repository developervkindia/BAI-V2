<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request, Card $card)
    {
        abort_unless($card->board->canEdit(auth()->user()), 403);

        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store("attachments/{$card->board_id}/{$card->id}", 'public');

        $attachment = Attachment::create([
            'attachable_type' => Card::class,
            'attachable_id' => $card->id,
            'user_id' => auth()->id(),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json($attachment, 201);
    }

    public function destroy(Attachment $attachment)
    {
        abort_if($attachment->user_id !== auth()->id(), 403);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    public function download(Attachment $attachment)
    {
        // Verify the user can access the board this attachment belongs to
        if ($attachment->attachable_type === Card::class) {
            $card = Card::find($attachment->attachable_id);
            abort_unless($card && $card->board->canAccess(auth()->user()), 403);
        }

        return Storage::disk('public')->download($attachment->path, $attachment->filename);
    }
}
