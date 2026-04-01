<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\HrPayrollEntry;
use App\Models\HrPayrollRun;
use App\Models\HrSalaryComponent;
use App\Models\HrSalaryStructure;
use Carbon\Carbon;

class HrPayrollService
{
    /**
     * Process payroll for all active employees.
     *
     * Gets the salary structure for each employee, computes earnings/deductions
     * from components, creates HrPayrollEntry records, and updates run totals.
     */
    public function calculatePayroll(HrPayrollRun $run): void
    {
        $orgId = $run->organization_id;

        // Get all active employees in the organization
        $employees = EmployeeProfile::where('organization_id', $orgId)
            ->where('status', 'active')
            ->get();

        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        $employeeCount = 0;

        foreach ($employees as $employee) {
            // Get current salary structure
            $structure = HrSalaryStructure::where('employee_profile_id', $employee->id)
                ->where('is_current', true)
                ->with('components.component')
                ->first();

            if (!$structure) {
                continue;
            }

            $earnings = [];
            $deductions = [];
            $grossEarnings = 0;
            $totalDeductionAmount = 0;

            // Process each salary component
            foreach ($structure->components as $structureComponent) {
                $component = $structureComponent->component;

                if (!$component || !$component->is_active) {
                    continue;
                }

                $monthlyAmount = (float) $structureComponent->monthly_amount;

                $componentData = [
                    'component_id' => $component->id,
                    'name' => $component->name,
                    'code' => $component->code,
                    'type' => $component->type,
                    'amount' => $monthlyAmount,
                    'is_taxable' => $component->is_taxable,
                ];

                if ($component->type === 'earning') {
                    $earnings[] = $componentData;
                    $grossEarnings += $monthlyAmount;
                } elseif ($component->type === 'deduction') {
                    $deductions[] = $componentData;
                    $totalDeductionAmount += $monthlyAmount;
                }
            }

            // Calculate statutory deductions
            $basicComponent = collect($earnings)->firstWhere('code', 'basic');
            $basicSalary = $basicComponent ? $basicComponent['amount'] : 0;

            // PF calculation
            $pf = $this->calculatePF($basicSalary);
            if ($pf['employee'] > 0) {
                $deductions[] = [
                    'name' => 'Employee PF',
                    'code' => 'epf_employee',
                    'type' => 'deduction',
                    'amount' => $pf['employee'],
                    'is_taxable' => false,
                ];
                $totalDeductionAmount += $pf['employee'];
            }

            // ESI calculation
            $esi = $this->calculateESI($grossEarnings);
            if ($esi['employee'] > 0) {
                $deductions[] = [
                    'name' => 'Employee ESI',
                    'code' => 'esi_employee',
                    'type' => 'deduction',
                    'amount' => $esi['employee'],
                    'is_taxable' => false,
                ];
                $totalDeductionAmount += $esi['employee'];
            }

            // Professional Tax
            $pt = $this->calculatePT($employee->work_location ?? '', $grossEarnings);
            if ($pt > 0) {
                $deductions[] = [
                    'name' => 'Professional Tax',
                    'code' => 'pt',
                    'type' => 'deduction',
                    'amount' => $pt,
                    'is_taxable' => false,
                ];
                $totalDeductionAmount += $pt;
            }

            // TDS calculation (estimate annual taxable income from monthly gross)
            $annualTaxableIncome = $grossEarnings * 12;
            $tds = $this->calculateTDS($annualTaxableIncome);
            if ($tds > 0) {
                $deductions[] = [
                    'name' => 'TDS',
                    'code' => 'tds',
                    'type' => 'deduction',
                    'amount' => $tds,
                    'is_taxable' => false,
                ];
                $totalDeductionAmount += $tds;
            }

            $netPay = $grossEarnings - $totalDeductionAmount;

            // Create payroll entry
            HrPayrollEntry::create([
                'hr_payroll_run_id' => $run->id,
                'employee_profile_id' => $employee->id,
                'gross_earnings' => $grossEarnings,
                'total_deductions' => $totalDeductionAmount,
                'net_pay' => $netPay,
                'working_days' => Carbon::create($run->year, $run->month, 1)->daysInMonth,
                'days_present' => Carbon::create($run->year, $run->month, 1)->daysInMonth,
                'lop_days' => 0,
                'components' => [
                    'earnings' => $earnings,
                    'deductions' => $deductions,
                    'employer_contributions' => [
                        'epf' => $pf['employer'],
                        'esi' => $esi['employer'],
                    ],
                ],
                'status' => 'calculated',
            ]);

            $totalGross += $grossEarnings;
            $totalDeductions += $totalDeductionAmount;
            $totalNet += $netPay;
            $employeeCount++;
        }

        // Update run totals
        $run->update([
            'total_gross' => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net' => $totalNet,
            'employee_count' => $employeeCount,
            'status' => 'processed',
            'processed_at' => Carbon::now(),
        ]);
    }

    /**
     * Calculate Provident Fund contributions.
     *
     * Employee contribution: 12% of basic salary.
     * Employer contribution: 12% of basic (3.67% EPF + 8.33% EPS).
     *
     * @return array{employee: float, employer: float}
     */
    public function calculatePF(float $basicSalary): array
    {
        $employeeContribution = round($basicSalary * 0.12, 2);
        $employerContribution = round($basicSalary * 0.12, 2); // 3.67% EPF + 8.33% EPS

        return [
            'employee' => $employeeContribution,
            'employer' => $employerContribution,
        ];
    }

    /**
     * Calculate ESI (Employee State Insurance) contributions.
     *
     * Applicable only if gross salary <= 21,000.
     * Employee: 0.75%, Employer: 3.25%.
     *
     * @return array{employee: float, employer: float}
     */
    public function calculateESI(float $grossSalary): array
    {
        if ($grossSalary > 21000) {
            return ['employee' => 0.0, 'employer' => 0.0];
        }

        return [
            'employee' => round($grossSalary * 0.0075, 2),
            'employer' => round($grossSalary * 0.0325, 2),
        ];
    }

    /**
     * Calculate monthly TDS based on annual taxable income.
     *
     * New regime slabs:
     *   0 - 3L: 0%, 3L - 7L: 5%, 7L - 10L: 10%,
     *   10L - 12L: 15%, 12L - 15L: 20%, 15L+: 30%
     *
     * Old regime slabs:
     *   0 - 2.5L: 0%, 2.5L - 5L: 5%, 5L - 10L: 20%, 10L+: 30%
     *
     * Returns monthly TDS (annual / 12).
     */
    public function calculateTDS(float $annualTaxableIncome, string $regime = 'new'): float
    {
        $annualTax = 0.0;

        if ($regime === 'new') {
            $slabs = [
                ['limit' => 300000, 'rate' => 0.00],
                ['limit' => 700000, 'rate' => 0.05],
                ['limit' => 1000000, 'rate' => 0.10],
                ['limit' => 1200000, 'rate' => 0.15],
                ['limit' => 1500000, 'rate' => 0.20],
                ['limit' => PHP_FLOAT_MAX, 'rate' => 0.30],
            ];
        } else {
            $slabs = [
                ['limit' => 250000, 'rate' => 0.00],
                ['limit' => 500000, 'rate' => 0.05],
                ['limit' => 1000000, 'rate' => 0.20],
                ['limit' => PHP_FLOAT_MAX, 'rate' => 0.30],
            ];
        }

        $remaining = $annualTaxableIncome;
        $previousLimit = 0;

        foreach ($slabs as $slab) {
            if ($remaining <= 0) {
                break;
            }

            $taxableInSlab = min($remaining, $slab['limit'] - $previousLimit);
            $annualTax += $taxableInSlab * $slab['rate'];

            $remaining -= $taxableInSlab;
            $previousLimit = $slab['limit'];
        }

        // Return monthly TDS
        return round($annualTax / 12, 2);
    }

    /**
     * Calculate Professional Tax by state.
     *
     * Maharashtra: > 10,000 gross = 200/month
     * Karnataka: > 15,000 gross = 200/month
     * Default: 0
     */
    public function calculatePT(string $state, float $monthlyGross): float
    {
        $state = strtolower(trim($state));

        return match (true) {
            str_contains($state, 'maharashtra') => $monthlyGross > 10000 ? 200.0 : 0.0,
            str_contains($state, 'karnataka') => $monthlyGross > 15000 ? 200.0 : 0.0,
            default => 0.0,
        };
    }

    /**
     * Return structured payslip data from the entry's components JSON.
     */
    public function generatePayslipData(HrPayrollEntry $entry): array
    {
        $entry->load('employeeProfile.user', 'payrollRun');

        $components = $entry->components ?? [];
        $earnings = $components['earnings'] ?? [];
        $deductions = $components['deductions'] ?? [];
        $employerContributions = $components['employer_contributions'] ?? [];

        $totalEarnings = collect($earnings)->sum('amount');
        $totalDeductions = collect($deductions)->sum('amount');

        return [
            'employee' => [
                'id' => $entry->employeeProfile->employee_id ?? null,
                'name' => $entry->employeeProfile->user->name ?? 'N/A',
                'designation' => $entry->employeeProfile->designation,
                'department' => $entry->employeeProfile->department,
                'bank_name' => $entry->employeeProfile->bank_name,
                'account_number' => $entry->employeeProfile->account_number,
                'ifsc_code' => $entry->employeeProfile->ifsc_code,
            ],
            'payroll' => [
                'month' => $entry->payrollRun->month,
                'year' => $entry->payrollRun->year,
                'working_days' => $entry->working_days,
                'days_present' => $entry->days_present,
                'lop_days' => $entry->lop_days,
            ],
            'earnings' => $earnings,
            'deductions' => $deductions,
            'employer_contributions' => $employerContributions,
            'summary' => [
                'gross_earnings' => (float) $entry->gross_earnings,
                'total_deductions' => (float) $entry->total_deductions,
                'net_pay' => (float) $entry->net_pay,
                'total_earnings_calculated' => $totalEarnings,
                'total_deductions_calculated' => $totalDeductions,
            ],
        ];
    }
}
