<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrSurvey;
use App\Models\HrSurveyQuestion;
use App\Models\HrSurveyResponse;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;

class HrSurveyApiController extends Controller
{
    /**
     * Create a new survey with questions.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:engagement,pulse,feedback,exit,custom',
            'is_anonymous' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|max:500',
            'questions.*.type' => 'required|string|in:text,rating,multiple_choice,single_choice,yes_no',
            'questions.*.options' => 'nullable|array',
            'questions.*.is_required' => 'boolean',
        ]);

        $survey = HrSurvey::create([
            'organization_id' => $org->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'status' => 'draft',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['questions'] as $index => $questionData) {
            $survey->questions()->create([
                'question' => $questionData['question'],
                'type' => $questionData['type'],
                'options' => $questionData['options'] ?? null,
                'is_required' => $questionData['is_required'] ?? true,
                'sort_order' => $index + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Survey created successfully.',
            'survey' => $survey->fresh()->load('questions'),
        ]);
    }

    /**
     * Publish a survey (set status to active).
     */
    public function publish(HrSurvey $survey)
    {
        abort_unless(auth()->check(), 401);

        if ($survey->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft surveys can be published.',
            ], 422);
        }

        $survey->update([
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Survey published successfully.',
            'survey' => $survey->fresh(),
        ]);
    }

    /**
     * Close a survey.
     */
    public function close(HrSurvey $survey)
    {
        abort_unless(auth()->check(), 401);

        if ($survey->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active surveys can be closed.',
            ], 422);
        }

        $survey->update([
            'status' => 'closed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Survey closed successfully.',
            'survey' => $survey->fresh(),
        ]);
    }

    /**
     * Submit responses for a survey.
     */
    public function submitResponse(Request $request, HrSurvey $survey)
    {
        abort_unless(auth()->check(), 401);

        if ($survey->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This survey is not currently active.',
            ], 422);
        }

        $org = auth()->user()->currentOrganization();
        $profile = EmployeeProfile::where('user_id', auth()->id())
            ->where('organization_id', $org->id)
            ->firstOrFail();

        // Check if already responded
        $alreadyResponded = HrSurveyResponse::where('hr_survey_id', $survey->id)
            ->where('employee_profile_id', $profile->id)
            ->exists();

        if ($alreadyResponded) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a response for this survey.',
            ], 422);
        }

        $validated = $request->validate([
            'responses' => 'required|array|min:1',
            'responses.*.question_id' => 'required|exists:hr_survey_questions,id',
            'responses.*.answer' => 'nullable|string',
            'responses.*.rating' => 'nullable|integer|min:1|max:5',
        ]);

        $created = [];

        foreach ($validated['responses'] as $responseData) {
            $created[] = HrSurveyResponse::create([
                'hr_survey_id' => $survey->id,
                'hr_survey_question_id' => $responseData['question_id'],
                'employee_profile_id' => $profile->id,
                'answer' => $responseData['answer'] ?? null,
                'rating' => $responseData['rating'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Survey response submitted successfully.',
            'responses' => $created,
        ]);
    }
}
