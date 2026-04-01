<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrJobPosting;
use Illuminate\Http\Request;

class HrRecruitmentController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();

        $postings = HrJobPosting::where('organization_id', $organization->id)
            ->withCount('candidates')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('hr.recruitment.index', compact('postings'));
    }

    public function showPosting(HrJobPosting $jobPosting)
    {
        $jobPosting->load(['candidates', 'department']);

        return view('hr.recruitment.show-posting', compact('jobPosting'));
    }

    public function pipeline(HrJobPosting $jobPosting)
    {
        $jobPosting->load(['candidates' => function ($q) {
            $q->orderBy('stage')->orderBy('created_at');
        }]);

        $pipeline = $jobPosting->candidates->groupBy('stage');

        return view('hr.recruitment.pipeline', compact('jobPosting', 'pipeline'));
    }
}
