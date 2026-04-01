<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\HrLeaveBalance;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrLeaveController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $employee = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $balances = collect();
        $recentRequests = collect();

        if ($employee) {
            $balances = HrLeaveBalance::where('employee_profile_id', $employee->id)
                ->with('leaveType')
                ->get();

            $recentRequests = HrLeaveRequest::where('employee_profile_id', $employee->id)
                ->with('leaveType')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('hr.leave.index', compact('balances', 'recentRequests', 'employee'));
    }

    public function apply()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $employee = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $leaveTypes = HrLeaveType::where('organization_id', $organization->id)
            ->where('is_active', true)
            ->get();

        $balances = collect();
        if ($employee) {
            $balances = HrLeaveBalance::where('employee_profile_id', $employee->id)
                ->with('leaveType')
                ->get()
                ->keyBy('leave_type_id');
        }

        return view('hr.leave.apply', compact('leaveTypes', 'balances', 'employee'));
    }

    public function my()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $employee = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $leaveHistory = collect();
        $balances = collect();

        if ($employee) {
            $leaveHistory = HrLeaveRequest::where('employee_profile_id', $employee->id)
                ->with('leaveType')
                ->orderBy('start_date', 'desc')
                ->paginate(20);

            $balances = HrLeaveBalance::where('employee_profile_id', $employee->id)
                ->with('leaveType')
                ->get();
        }

        return view('hr.leave.my', compact('leaveHistory', 'balances', 'employee'));
    }

    public function calendar(Request $request)
    {
        $organization = auth()->user()->currentOrganization();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $leaveRequests = HrLeaveRequest::whereHas('employeeProfile', function ($q) use ($organization) {
                $q->where('organization_id', $organization->id);
            })
            ->where('status', 'approved')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                      $q2->where('start_date', '<=', $startOfMonth)
                         ->where('end_date', '>=', $endOfMonth);
                  });
            })
            ->with(['employeeProfile', 'leaveType'])
            ->get();

        return view('hr.leave.calendar', compact('leaveRequests', 'month', 'year'));
    }

    public function approvals()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $manager = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $pendingRequests = collect();

        if ($manager) {
            $directReportIds = EmployeeProfile::where('reporting_manager_id', $manager->id)
                ->pluck('id');

            $pendingRequests = HrLeaveRequest::whereIn('employee_profile_id', $directReportIds)
                ->where('status', 'pending')
                ->with(['employeeProfile', 'leaveType'])
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('hr.leave.approvals', compact('pendingRequests'));
    }
}
