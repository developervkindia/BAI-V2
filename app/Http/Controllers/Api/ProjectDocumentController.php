<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\ProjectFolder;
use Illuminate\Http\Request;

class ProjectDocumentController extends Controller
{
    public function index(Project $project)
    {
        abort_unless($project->canAccess(auth()->user()), 403);

        $folders = $project->folders()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $attachments = $project->attachments()
            ->whereNull('project_folder_id')
            ->latest()
            ->get();

        return response()->json([
            'folders' => $folders,
            'attachments' => $attachments,
        ]);
    }

    public function showFolder(ProjectFolder $folder)
    {
        abort_unless($folder->project->canAccess(auth()->user()), 403);

        $children = $folder->children()->orderBy('name')->get();
        $attachments = $folder->attachments()->latest()->get();

        return response()->json([
            'folder' => $folder,
            'children' => $children,
            'attachments' => $attachments,
        ]);
    }

    public function createFolder(Request $request, Project $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:project_folders,id',
        ]);

        $folder = $project->folders()->create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return response()->json($folder, 201);
    }

    public function updateFolder(Request $request, ProjectFolder $folder)
    {
        abort_unless($folder->project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder->update($validated);

        return response()->json($folder);
    }

    public function deleteFolder(ProjectFolder $folder)
    {
        abort_unless($folder->project->canEdit(auth()->user()), 403);

        $folder->attachments()->delete();
        $folder->children()->each(function ($child) {
            $child->attachments()->delete();
            $child->delete();
        });
        $folder->delete();

        return response()->json(['message' => 'Folder and contents deleted.']);
    }

    public function upload(Request $request, Project $project)
    {
        abort_unless($project->canEdit(auth()->user()), 403);

        $validated = $request->validate([
            'file' => 'required|file|max:51200',
            'project_folder_id' => 'nullable|exists:project_folders,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('project-documents/' . $project->id, 'public');

        $attachment = $project->attachments()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'project_folder_id' => $validated['project_folder_id'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json($attachment, 201);
    }
}
