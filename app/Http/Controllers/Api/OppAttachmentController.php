<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OppAttachment;
use App\Models\OppTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OppAttachmentController extends Controller
{
    /**
     * Upload an attachment to a task.
     */
    public function store(Request $request, OppTask $task)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB max
        ]);

        $file = $request->file('file');
        $path = $file->store("opp-attachments/{$task->project_id}", 'public');

        $attachment = OppAttachment::create([
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'uploaded_by' => auth()->id(),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json(['attachment' => $attachment], 201);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(OppAttachment $attachment)
    {
        Storage::disk('public')->delete($attachment->path);

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted.']);
    }
}
