<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectAttachmentController extends Controller
{
    public function store(Request $request, ProjectTask $task)
    {
        abort_unless($task->project->canEdit(auth()->user()), 403);

        $request->validate(['file' => 'required|file|max:20480']);

        $file    = $request->file('file');
        $path    = $file->store("attachments/projects/{$task->project_id}/{$task->id}", 'public');

        $attachment = Attachment::create([
            'attachable_type' => ProjectTask::class,
            'attachable_id'   => $task->id,
            'user_id'         => auth()->id(),
            'filename'        => $file->getClientOriginalName(),
            'path'            => $path,
            'mime_type'       => $file->getMimeType(),
            'size'            => $file->getSize(),
        ]);

        return response()->json([
            'id'       => $attachment->id,
            'filename' => $attachment->filename,
            'url'      => Storage::disk('public')->url($attachment->path),
            'size_fmt' => $this->formatBytes($attachment->size),
            'mime_type'=> $attachment->mime_type,
        ], 201);
    }

    public function destroy(Attachment $attachment)
    {
        // Allow owner or project manager to delete
        $task = ProjectTask::find($attachment->attachable_id);
        abort_unless(
            $attachment->user_id === auth()->id() ||
            ($task && $task->project->isManager(auth()->user())),
            403
        );

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    public function download(Attachment $attachment)
    {
        if ($attachment->attachable_type === ProjectTask::class) {
            $task = ProjectTask::find($attachment->attachable_id);
            abort_unless($task && $task->project->canAccess(auth()->user()), 403);
        }

        return Storage::disk('public')->download($attachment->path, $attachment->filename);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
