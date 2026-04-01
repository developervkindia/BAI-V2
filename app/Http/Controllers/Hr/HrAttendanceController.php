<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\HrAttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $employeeId = $request->input('employee_id');

        if ($employeeId) {
            $employee = EmployeeProfile::where('organization_id', $organization->id)
                ->findOrFail($employeeId);
        } else {
            $employee = EmployeeProfile::where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->first();
        }

        $attendanceLogs = collect();
        if ($employee) {
            $attendanceLogs = HrAttendanceLog::where('employee_profile_id', $employee->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get();
        }

        $employees = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();

        return view('hr.attendance.index', compact('employee', 'attendanceLogs', 'employees', 'month', 'year'));
    }

    public function my()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $employee = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $todayLog = null;
        if ($employee) {
            $todayLog = HrAttendanceLog::where('employee_profile_id', $employee->id)
                ->where('date', Carbon::today())
                ->first();
        }

        return view('hr.attendance.my', compact('employee', 'todayLog'));
    }

    public function team(Request $request)
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $manager = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $directReports = collect();
        $attendanceGrid = collect();

        if ($manager) {
            $directReports = EmployeeProfile::where('organization_id', $organization->id)
                ->where('reporting_manager_id', $manager->id)
                ->where('status', 'active')
                ->orderByDesc('created_at')
                ->get();

            $reportIds = $directReports->pluck('id');

            $attendanceGrid = HrAttendanceLog::whereIn('employee_profile_id', $reportIds)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get()
                ->groupBy('employee_profile_id');
        }

        return view('hr.attendance.team', compact('directReports', 'attendanceGrid', 'month', 'year'));
    }

    public function reports(Request $request)
    {
        $organization = auth()->user()->currentOrganization();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $departmentId = $request->input('department_id');

        $query = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active');

        if ($departmentId) {
            $query->where('hr_department_id', $departmentId);
        }

        $employees = $query->orderByDesc('created_at')->get();

        $employeeIds = $employees->pluck('id');

        $attendanceSummary = HrAttendanceLog::whereIn('employee_profile_id', $employeeIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('employee_profile_id');

        return view('hr.attendance.reports', compact('employees', 'attendanceSummary', 'month', 'year'));
    }
}
