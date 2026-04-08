<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocFolder;
use App\Services\DocsQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocFolderApiController extends Controller
{
    public function __construct(private DocsQuotaService $quota) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:doc_folders,id',
            'color' => 'nullable|string|max:20',
        ]);

        $org = auth()->user()->currentOrganization();

        if (! $this->quota->canCreateFolder($org)) {
            return response()->json(['success' => false, 'message' => 'Folder limit reached.'], 422);
        }

        $folder = DocFolder::create([
            'organization_id' => $org->id,
            'created_by' => auth()->id(),
            'name' => $request->input('name'),
            'parent_id' => $request->input('parent_id'),
            'color' => $request->input('color'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $folder,
        ], 201);
    }

    public function update(Request $request, DocFolder $folder): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|integer|exists:doc_folders,id',
            'color' => 'nullable|string|max:20',
        ]);

        $folder->update($request->only(['name', 'parent_id', 'color']));

        return response()->json(['success' => true, 'data' => $folder]);
    }

    public function destroy(DocFolder $folder): JsonResponse
    {
        $folder->delete();

        return response()->json(['success' => true, 'message' => 'Folder deleted.']);
    }

    public function tree(Request $request): JsonResponse
    {
        $org = auth()->user()->currentOrganization();

        $folders = DocFolder::where('organization_id', $org->id)
            ->whereNull('parent_id')
            ->with('children.children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $folders]);
    }
}
