<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\EmployeeEducation;
use App\Models\EmployeeExperience;
use App\Models\EmployeeDocument;
use App\Models\EmployeeAsset;
use App\Models\EmployeeSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeProfileApiController extends Controller
{
    /**
     * Check if the current user can manage the given profile.
     */
    protected function canManageProfile(EmployeeProfile $profile): bool
    {
        if ($profile->user_id === auth()->id()) {
            return true;
        }

        return $profile->organization->isAdmin(auth()->user());
    }

    // ─── Education ──────────────────────────────────────────────

    public function storeEducation(Request $request, EmployeeProfile $profile)
    {
        abort_unless($this->canManageProfile($profile), 403);

        $validated = $request->validate([
            'degree' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_year' => 'nullable|integer|min:1950|max:2100',
            'end_year' => 'nullable|integer|min:1950|max:2100',
            'grade' => 'nullable|string|max:50',
        ]);

        $education = $profile->education()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Education added successfully.',
            'education' => $education,
        ]);
    }

    public function updateEducation(Request $request, EmployeeEducation $education)
    {
        abort_unless($this->canManageProfile($education->employeeProfile), 403);

        $validated = $request->validate([
            'degree' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_year' => 'nullable|integer|min:1950|max:2100',
            'end_year' => 'nullable|integer|min:1950|max:2100',
            'grade' => 'nullable|string|max:50',
        ]);

        $education->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Education updated successfully.',
            'education' => $education,
        ]);
    }

    public function destroyEducation(EmployeeEducation $education)
    {
        abort_unless($this->canManageProfile($education->employeeProfile), 403);

        $education->delete();

        return response()->json([
            'success' => true,
            'message' => 'Education deleted successfully.',
        ]);
    }

    // ─── Experience ─────────────────────────────────────────────

    public function storeExperience(Request $request, EmployeeProfile $profile)
    {
        abort_unless($this->canManageProfile($profile), 403);

        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:2000',
        ]);

        $experience = $profile->experience()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Experience added successfully.',
            'experience' => $experience,
        ]);
    }

    public function updateExperience(Request $request, EmployeeExperience $experience)
    {
        abort_unless($this->canManageProfile($experience->employeeProfile), 403);

        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:2000',
        ]);

        $experience->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Experience updated successfully.',
            'experience' => $experience,
        ]);
    }

    public function destroyExperience(EmployeeExperience $experience)
    {
        abort_unless($this->canManageProfile($experience->employeeProfile), 403);

        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully.',
        ]);
    }

    // ─── Documents ──────────────────────────────────────────────

    public function storeDocument(Request $request, EmployeeProfile $profile)
    {
        abort_unless($this->canManageProfile($profile), 403);

        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:100',
            'file' => 'required|file|max:10240',
            'expiry_date' => 'nullable|date',
        ]);

        $path = $request->file('file')->store(
            "employee-documents/{$profile->id}",
            'private'
        );

        $document = $profile->documents()->create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'document_number' => $validated['document_number'] ?? null,
            'file_path' => $path,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_size' => $request->file('file')->getSize(),
            'expiry_date' => $validated['expiry_date'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
            'document' => $document,
        ]);
    }

    public function destroyDocument(EmployeeDocument $document)
    {
        abort_unless($this->canManageProfile($document->employeeProfile), 403);

        // Delete the file from storage
        if ($document->file_path) {
            Storage::disk('private')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }

    // ─── Assets ─────────────────────────────────────────────────

    public function storeAsset(Request $request, EmployeeProfile $profile)
    {
        // Admin only for asset management
        abort_unless($profile->organization->isAdmin(auth()->user()), 403);

        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'asset_tag' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'assigned_date' => 'nullable|date',
            'return_date' => 'nullable|date|after_or_equal:assigned_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $asset = $profile->assets()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asset assigned successfully.',
            'asset' => $asset,
        ]);
    }

    public function updateAsset(Request $request, EmployeeAsset $asset)
    {
        abort_unless($this->canManageProfile($asset->employeeProfile), 403);

        $validated = $request->validate([
            'type' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'asset_tag' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'assigned_date' => 'nullable|date',
            'return_date' => 'nullable|date|after_or_equal:assigned_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $asset->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asset updated successfully.',
            'asset' => $asset,
        ]);
    }

    public function destroyAsset(EmployeeAsset $asset)
    {
        abort_unless($this->canManageProfile($asset->employeeProfile), 403);

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asset removed successfully.',
        ]);
    }

    // ─── Skills ─────────────────────────────────────────────────

    public function storeSkill(Request $request, EmployeeProfile $profile)
    {
        abort_unless($this->canManageProfile($profile), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'issued_by' => 'nullable|string|max:255',
            'issued_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issued_date',
        ]);

        $skill = $profile->skills()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Skill added successfully.',
            'skill' => $skill,
        ]);
    }

    public function destroySkill(EmployeeSkill $skill)
    {
        abort_unless($this->canManageProfile($skill->employeeProfile), 403);

        $skill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skill removed successfully.',
        ]);
    }
}
