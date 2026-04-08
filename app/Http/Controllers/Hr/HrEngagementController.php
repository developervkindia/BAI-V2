<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\HrAnnouncement;
use App\Models\HrRecognition;
use Carbon\Carbon;

class HrEngagementController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();

        $announcements = HrAnnouncement::where('organization_id', $organization->id)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recognitions = HrRecognition::whereHas('employeeProfile', function ($q) use ($organization) {
                $q->where('organization_id', $organization->id);
            })
            ->with('employeeProfile')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);

        $upcomingBirthdays = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_birth')
            ->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [
                $today->format('m-d'),
                $nextWeek->format('m-d'),
            ])
            ->limit(10)
            ->get();

        $upcomingAnniversaries = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_joining')
            ->whereRaw("DATE_FORMAT(date_of_joining, '%m-%d') BETWEEN ? AND ?", [
                $today->format('m-d'),
                $nextWeek->format('m-d'),
            ])
            ->where('date_of_joining', '<', $today->copy()->subYear())
            ->limit(10)
            ->get();

        return view('hr.engagement.index', compact('announcements', 'recognitions', 'upcomingBirthdays', 'upcomingAnniversaries'));
    }

    public function birthdays()
    {
        $organization = auth()->user()->currentOrganization();

        $today = Carbon::today();
        $nextMonth = Carbon::today()->addDays(30);

        $upcomingBirthdays = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_birth')
            ->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [
                $today->format('m-d'),
                $nextMonth->format('m-d'),
            ])
            ->orderByRaw("DATE_FORMAT(date_of_birth, '%m-%d')")
            ->get();

        return view('hr.engagement.birthdays', compact('upcomingBirthdays'));
    }

    public function anniversaries()
    {
        $organization = auth()->user()->currentOrganization();

        $today = Carbon::today();
        $nextMonth = Carbon::today()->addDays(30);

        $upcomingAnniversaries = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_joining')
            ->whereRaw("DATE_FORMAT(date_of_joining, '%m-%d') BETWEEN ? AND ?", [
                $today->format('m-d'),
                $nextMonth->format('m-d'),
            ])
            ->where('date_of_joining', '<', $today->copy()->subYear())
            ->orderByRaw("DATE_FORMAT(date_of_joining, '%m-%d')")
            ->get();

        return view('hr.engagement.anniversaries', compact('upcomingAnniversaries'));
    }
}
