<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\HrPayrollEntry;
use App\Models\HrPayrollRun;
use App\Models\HrSalaryComponent;
use App\Models\HrSalaryStructure;
use Illuminate\Http\Request;

class HrPayrollController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();

        $recentRuns = HrPayrollRun::where('organization_id', $organization->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $totalEmployees = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->count();

        $latestRun = $recentRuns->first();

        return view('hr.payroll.index', compact('recentRuns', 'totalEmployees', 'latestRun'));
    }

    public function run()
    {
        $organization = auth()->user()->currentOrganization();

        $employees = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return view('hr.payroll.run', compact('employees'));
    }

    public function showRun(HrPayrollRun $payrollRun)
    {
        $payrollRun->load(['entries.employeeProfile']);

        return view('hr.payroll.show-run', compact('payrollRun'));
    }

    public function payslip(HrPayrollEntry $payrollEntry)
    {
        $payrollEntry->load(['employeeProfile', 'payrollRun']);

        return view('hr.payroll.payslip', compact('payrollEntry'));
    }

    public function myPayslips()
    {
        $organization = auth()->user()->currentOrganization();
        $user = auth()->user();

        $employee = EmployeeProfile::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        $payslips = collect();

        if ($employee) {
            $payslips = HrPayrollEntry::where('employee_profile_id', $employee->id)
                ->with('payrollRun')
                ->orderBy('created_at', 'desc')
                ->paginate(12);
        }

        return view('hr.payroll.my-payslips', compact('payslips', 'employee'));
    }

    public function salaryComponents()
    {
        $organization = auth()->user()->currentOrganization();

        $components = HrSalaryComponent::where('organization_id', $organization->id)
            ->orderBy('sort_order')
            ->get();

        return view('hr.payroll.salary-components', compact('components'));
    }

    public function salaryStructures()
    {
        $organization = auth()->user()->currentOrganization();

        $employees = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->with(['user', 'currentSalaryStructure'])
            ->orderByDesc('created_at')
            ->get();

        return view('hr.payroll.salary-structures', compact('employees'));
    }

    public function editSalaryStructure(EmployeeProfile $employeeProfile)
    {
        $organization = auth()->user()->currentOrganization();

        $employeeProfile->load(['user', 'currentSalaryStructure.components.component']);

        $components = HrSalaryComponent::where('organization_id', $organization->id)
            ->orderBy('sort_order')
            ->get();

        return view('hr.payroll.edit-salary-structure', compact('employeeProfile', 'components'));
    }
}
