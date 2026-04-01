<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrSurvey;
use Illuminate\Http\Request;

class HrSurveyController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();

        $surveys = HrSurvey::where('organization_id', $organization->id)
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('hr.surveys.index', compact('surveys'));
    }

    public function create()
    {
        return view('hr.surveys.create');
    }

    public function show(HrSurvey $survey)
    {
        $survey->load(['questions', 'responses']);

        return view('hr.surveys.show', compact('survey'));
    }

    public function respond(HrSurvey $survey)
    {
        $survey->load('questions');

        return view('hr.surveys.respond', compact('survey'));
    }
}
