<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrRecognition;
use Illuminate\Http\Request;

class HrRecognitionApiController extends Controller
{
    /**
     * Create a new recognition.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'employee_profile_id' => 'required|exists:employee_profiles,id',
            'type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'badge_icon' => 'nullable|string|max:100',
        ]);

        $recognition = HrRecognition::create(array_merge($validated, [
            'organization_id' => $org->id,
            'recognized_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Recognition created successfully.',
            'recognition' => $recognition->load('employeeProfile', 'recognizer'),
        ]);
    }

    /**
     * Delete a recognition.
     */
    public function destroy(HrRecognition $recognition)
    {
        abort_unless(auth()->check(), 401);

        $recognition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recognition deleted successfully.',
        ]);
    }
}
