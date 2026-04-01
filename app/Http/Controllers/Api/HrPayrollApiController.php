<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrPayrollRun;
use App\Models\HrPayrollEntry;
use App\Models\HrSalaryComponent;
use App\Models\HrSalaryStructure;
use App\Models\HrSalaryStructureComponent;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrPayrollApiController extends Controller
{
    /**
     * Process a payroll run.
     */
    public function processRun(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for existing run
        $existing = HrPayrollRun::where('organization_id', $org->id)
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'A payroll run already exists for this month/year.',
            ], 422);
        }

        // Create payroll run
        $run = HrPayrollRun::create([
            'organization_id' => $org->id,
            'month' => $validated['month'],
            'year' => $validated['year'],
            'status' => 'draft',
            'processed_by' => auth()->id(),
            'processed_at' => Carbon::now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Get all active employees
        $employees = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->get();

        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;

        foreach ($employees as $employee) {
            // Get current salary structure
            $structure = HrSalaryStructure::where('employee_profile_id', $employee->id)
                ->where('is_current', true)
                ->with('components.component')
                ->first();

            if (!$structure) {
                continue;
            }

            $grossEarnings = 0;
            $deductions = 0;
            $componentsData = [];

            foreach ($structure->components as $structureComponent) {
                $component = $structureComponent->component;
                $monthlyAmount = (float) $structureComponent->monthly_amount;

                $componentsData[] = [
                    'component_id' => $component->id,
                    'name' => $component->name,
                    'code' => $component->code,
                    'type' => $component->type,
                    'amount' => $monthlyAmount,
                ];

                if ($component->type === 'earning') {
                    $grossEarnings += $monthlyAmount;
                } elseif ($component->type === 'deduction') {
                    $deductions += $monthlyAmount;
                }
            }

            $netPay = $grossEarnings - $deductions;

            HrPayrollEntry::create([
                'hr_payroll_run_id' => $run->id,
                'employee_profile_id' => $employee->id,
                'gross_earnings' => $grossEarnings,
                'total_deductions' => $deductions,
                'net_pay' => $netPay,
                'working_days' => 30,
                'days_present' => 30,
                'lop_days' => 0,
                'components' => $componentsData,
                'status' => 'processed',
            ]);

            $totalGross += $grossEarnings;
            $totalDeductions += $deductions;
            $totalNet += $netPay;
        }

        $run->update([
            'total_gross' => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net' => $totalNet,
            'employee_count' => $employees->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll run processed successfully.',
            'run' => $run->fresh()->load('entries.employeeProfile'),
        ]);
    }

    /**
     * Finalize a payroll run.
     */
    public function finalizeRun(HrPayrollRun $run)
    {
        abort_unless(auth()->check(), 401);

        if ($run->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft payroll runs can be finalized.',
            ], 422);
        }

        $run->update([
            'status' => 'finalized',
            'finalized_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll run finalized.',
            'run' => $run->fresh(),
        ]);
    }

    /**
     * Mark a payroll run as paid.
     */
    public function markPaid(HrPayrollRun $run)
    {
        abort_unless(auth()->check(), 401);

        if ($run->status !== 'finalized') {
            return response()->json([
                'success' => false,
                'message' => 'Only finalized payroll runs can be marked as paid.',
            ], 422);
        }

        $run->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll run marked as paid.',
            'run' => $run->fresh(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // SALARY COMPONENTS
    // ═══════════════════════════════════════════════════════════════

    public function listComponents()
    {
        abort_unless(auth()->check(), 401);
        $org = auth()->user()->currentOrganization();

        return response()->json([
            'success' => true,
            'components' => HrSalaryComponent::where('organization_id', $org->id)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function storeComponent(Request $request)
    {
        abort_unless(auth()->check(), 401);
        $org = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'type' => 'required|in:earning,deduction,employer_contribution',
            'calculation_type' => 'required|in:fixed,percentage',
            'percentage_of' => 'nullable|string|max:50',
            'is_taxable' => 'boolean',
            'is_statutory' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $component = HrSalaryComponent::create(array_merge($validated, [
            'organization_id' => $org->id,
        ]));

        return response()->json(['success' => true, 'component' => $component]);
    }

    public function updateComponent(Request $request, HrSalaryComponent $component)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'type' => 'required|in:earning,deduction,employer_contribution',
            'calculation_type' => 'required|in:fixed,percentage',
            'percentage_of' => 'nullable|string|max:50',
            'is_taxable' => 'boolean',
            'is_statutory' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $component->update($validated);

        return response()->json(['success' => true, 'component' => $component->fresh()]);
    }

    public function deleteComponent(HrSalaryComponent $component)
    {
        abort_unless(auth()->check(), 401);
        $component->delete();
        return response()->json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════════════════
    // SALARY STRUCTURES (per employee)
    // ═══════════════════════════════════════════════════════════════

    public function getStructure(EmployeeProfile $profile)
    {
        abort_unless(auth()->check(), 401);
        $org = auth()->user()->currentOrganization();

        $structure = HrSalaryStructure::where('employee_profile_id', $profile->id)
            ->where('is_current', true)
            ->with('components.component')
            ->first();

        $allComponents = HrSalaryComponent::where('organization_id', $org->id)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'structure' => $structure,
            'components' => $allComponents,
        ]);
    }

    public function saveStructure(Request $request, EmployeeProfile $profile)
    {
        abort_unless(auth()->check(), 401);
        $org = auth()->user()->currentOrganization();

        $validated = $request->validate([
            'annual_ctc' => 'required|numeric|min:0',
            'effective_from' => 'nullable|date',
            'components' => 'required|array|min:1',
            'components.*.component_id' => 'required|exists:hr_salary_components,id',
            'components.*.monthly_amount' => 'required|numeric|min:0',
            'components.*.annual_amount' => 'required|numeric|min:0',
        ]);

        // Deactivate old structure
        HrSalaryStructure::where('employee_profile_id', $profile->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        // Create new structure
        $structure = HrSalaryStructure::create([
            'organization_id' => $org->id,
            'employee_profile_id' => $profile->id,
            'annual_ctc' => $validated['annual_ctc'],
            'effective_from' => $validated['effective_from'] ?? now()->toDateString(),
            'is_current' => true,
        ]);

        foreach ($validated['components'] as $comp) {
            HrSalaryStructureComponent::create([
                'hr_salary_structure_id' => $structure->id,
                'hr_salary_component_id' => $comp['component_id'],
                'monthly_amount' => $comp['monthly_amount'],
                'annual_amount' => $comp['annual_amount'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Salary structure saved.',
            'structure' => $structure->fresh()->load('components.component'),
        ]);
    }
}
