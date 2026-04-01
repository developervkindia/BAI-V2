<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrDepartment;
use Illuminate\Http\Request;

class HrDepartmentController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization();

        $departments = HrDepartment::where('organization_id', $organization->id)
            ->with(['head', 'parent'])
            ->withCount('employees')
            ->orderBy('name')
            ->get();

        return view('hr.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $organization = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:hr_departments,id',
            'head_id' => 'nullable|exists:employee_profiles,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['organization_id'] = $organization->id;

        $department = HrDepartment::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['department' => $department], 201);
        }

        return redirect()->route('hr.departments.index')->with('success', 'Department created successfully.');
    }

    public function update(Request $request, HrDepartment $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:hr_departments,id',
            'head_id' => 'nullable|exists:employee_profiles,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $department->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['department' => $department]);
        }

        return redirect()->route('hr.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(HrDepartment $department)
    {
        $department->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Department deleted successfully.']);
        }

        return redirect()->route('hr.departments.index')->with('success', 'Department deleted successfully.');
    }
}
