<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrJobPosting;
use App\Models\HrCandidate;
use App\Models\HrInterview;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrRecruitmentApiController extends Controller
{
    /**
     * Create a new job posting.
     */
    public function storePosting(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'hr_department_id' => 'required|exists:hr_departments,id',
            'hr_designation_id' => 'nullable|exists:hr_designations,id',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'employment_type' => 'required|string|in:full_time,part_time,contract,intern',
            'location' => 'nullable|string|max:255',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
            'positions' => 'nullable|integer|min:1',
        ]);

        $posting = HrJobPosting::create(array_merge($validated, [
            'organization_id' => $org->id,
            'status' => 'draft',
            'posted_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Job posting created successfully.',
            'posting' => $posting->load('department', 'designation'),
        ]);
    }

    /**
     * Create a new candidate.
     */
    public function storeCandidate(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'hr_job_posting_id' => 'required|exists:hr_job_postings,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'resume_path' => 'nullable|string',
            'source' => 'nullable|string|max:100',
            'current_company' => 'nullable|string|max:255',
            'current_designation' => 'nullable|string|max:255',
            'experience_years' => 'nullable|numeric|min:0',
            'expected_ctc' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $candidate = HrCandidate::create(array_merge($validated, [
            'organization_id' => $org->id,
            'stage' => 'applied',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Candidate added successfully.',
            'candidate' => $candidate->load('jobPosting'),
        ]);
    }

    /**
     * Move a candidate to a different stage.
     */
    public function moveCandidate(Request $request, HrCandidate $candidate)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'stage' => 'required|string|in:applied,screening,interview,assessment,offer,hired,rejected',
        ]);

        $candidate->update([
            'stage' => $validated['stage'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate moved to ' . $validated['stage'] . ' stage.',
            'candidate' => $candidate->fresh(),
        ]);
    }

    /**
     * Schedule an interview.
     */
    public function scheduleInterview(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'hr_candidate_id' => 'required|exists:hr_candidates,id',
            'interviewer_id' => 'required|exists:users,id',
            'round' => 'required|integer|min:1|max:10',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'mode' => 'required|string|in:in_person,video,phone',
        ]);

        $interview = HrInterview::create(array_merge($validated, [
            'status' => 'scheduled',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Interview scheduled successfully.',
            'interview' => $interview->load('candidate', 'interviewer'),
        ]);
    }

    /**
     * Submit interview feedback.
     */
    public function submitFeedback(Request $request, HrInterview $interview)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'required|string|max:2000',
            'decision' => 'required|string|in:advance,hold,reject',
        ]);

        $interview->update([
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'],
            'decision' => $validated['decision'],
            'status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interview feedback submitted.',
            'interview' => $interview->fresh()->load('candidate'),
        ]);
    }
}
