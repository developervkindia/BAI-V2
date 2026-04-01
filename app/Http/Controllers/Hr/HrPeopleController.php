<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\HrDepartment;
use Illuminate\Http\Request;

class HrPeopleController extends Controller
{
    public function index(Request $request)
    {
        $organization = auth()->user()->currentOrganization();

        $query = EmployeeProfile::where('organization_id', $organization->id)
            ->with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('department')) {
            $query->where('hr_department_id', $request->input('department'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->input('employment_type'));
        }

        $employees = $query->orderByDesc('created_at')->paginate(24);

        $departments = HrDepartment::where('organization_id', $organization->id)->get();

        $viewMode = $request->input('view', 'grid');

        return view('hr.people.index', compact('employees', 'departments', 'viewMode'));
    }

    public function show(EmployeeProfile $employeeProfile)
    {
        $employeeProfile->load([
            'education',
            'experience',
            'documents',
            'assets',
            'skills',
        ]);

        return view('hr.people.show', ['employee' => $employeeProfile]);
    }

    public function orgChart()
    {
        $organization = auth()->user()->currentOrganization();

        $employees = EmployeeProfile::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->with('reportingManager')
            ->get();

        return view('hr.people.org-chart', compact('employees'));
    }
}
