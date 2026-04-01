<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\HrExpenseCategory;
use App\Models\HrExpenseClaim;
use Illuminate\Http\Request;

class HrExpenseController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();

        $claims = HrExpenseClaim::whereHas('employeeProfile', function ($q) use ($organization) {
                $q->where('organization_id', $organization->id);
            })
            ->with(['employeeProfile', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('hr.expenses.index', compact('claims'));
    }

    public function create()
    {
        $organization = auth()->user()->currentOrganization();

        $categories = HrExpenseCategory::where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hr.expenses.create', compact('categories'));
    }

    public function show(HrExpenseClaim $expenseClaim)
    {
        $expenseClaim->load(['employeeProfile', 'items', 'approver']);

        return view('hr.expenses.show', compact('expenseClaim'));
    }

    public function my()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $employee = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $claims = collect();

        if ($employee) {
            $claims = HrExpenseClaim::where('employee_profile_id', $employee->id)
                ->with('items')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('hr.expenses.my', compact('claims', 'employee'));
    }

    public function approvals()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $manager = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $pendingClaims = collect();

        if ($manager) {
            $directReportIds = EmployeeProfile::where('reporting_manager_id', $manager->id)
                ->pluck('id');

            $pendingClaims = HrExpenseClaim::whereIn('employee_profile_id', $directReportIds)
                ->where('status', 'pending')
                ->with(['employeeProfile', 'items'])
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('hr.expenses.approvals', compact('pendingClaims'));
    }
}
