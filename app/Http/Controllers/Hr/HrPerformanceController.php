<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\HrGoal;
use App\Models\HrReview;
use App\Models\HrReviewCycle;
use Illuminate\Http\Request;

class HrPerformanceController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $employee = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $activeCycles = HrReviewCycle::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->get();

        $myGoals = collect();
        $myReviewStatus = collect();

        if ($employee) {
            $myGoals = HrGoal::where('employee_profile_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $myReviewStatus = HrReview::where('employee_profile_id', $employee->id)
                ->with('reviewCycle')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('hr.performance.index', compact('activeCycles', 'myGoals', 'myReviewStatus', 'employee'));
    }

    public function cycles()
    {
        $organization = auth()->user()->currentOrganization();

        $cycles = HrReviewCycle::where('organization_id', $organization->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hr.performance.cycles', compact('cycles'));
    }

    public function showCycle(HrReviewCycle $reviewCycle)
    {
        $reviewCycle->load(['reviews.employeeProfile']);

        return view('hr.performance.show-cycle', compact('reviewCycle'));
    }

    public function myReview()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $employee = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $currentCycle = HrReviewCycle::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $review = null;
        $goals = collect();

        if ($employee && $currentCycle) {
            $review = HrReview::where('employee_profile_id', $employee->id)
                ->where('hr_review_cycle_id', $currentCycle->id)
                ->with('ratings')
                ->first();

            $goals = HrGoal::where('employee_profile_id', $employee->id)
                ->get();
        }

        return view('hr.performance.my-review', compact('currentCycle', 'review', 'goals', 'employee'));
    }
}
