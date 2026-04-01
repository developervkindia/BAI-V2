<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\HrDepartment;
use App\Models\HrExit;
use Carbon\Carbon;

class HrDashboardController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();

        $totalEmployees = EmployeeProfile::where('organization_id', $organization->id)->count();
        $activeCount = EmployeeProfile::where('organization_id', $organization->id)->where('status', 'active')->count();
        $inactiveCount = EmployeeProfile::where('organization_id', $organization->id)->where('status', 'inactive')->count();
        $onLeaveCount = EmployeeProfile::where('organization_id', $organization->id)->where('status', 'on_leave')->count();

        $departmentBreakdown = HrDepartment::where('organization_id', $organization->id)
            ->withCount('employees')
            ->get();

        $newJoiners = EmployeeProfile::where('organization_id', $organization->id)
            ->where('date_of_joining', '>=', Carbon::now()->subDays(30))
            ->orderBy('date_of_joining', 'desc')
            ->get();

        $recentExits = HrExit::where('organization_id', $organization->id)
            ->with('employeeProfile.user')
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
            ->get();

        $upcomingAnniversaries = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_joining')
            ->whereRaw("DATE_FORMAT(date_of_joining, '%m-%d') BETWEEN ? AND ?", [
                $today->format('m-d'),
                $nextWeek->format('m-d'),
            ])
            ->where('date_of_joining', '<', $today->copy()->subYear())
            ->get();

        return view('hr.dashboard', compact(
            'totalEmployees',
            'activeCount',
            'inactiveCount',
            'onLeaveCount',
            'departmentBreakdown',
            'newJoiners',
            'recentExits',
            'upcomingBirthdays',
            'upcomingAnniversaries'
        ));
    }
}
