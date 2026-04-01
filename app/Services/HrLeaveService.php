<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\HrCompOff;
use App\Models\HrLeaveBalance;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HrLeaveService
{
    /**
     * Calculate business days between dates (excluding weekends).
     * If half day, return 0.5.
     */
    public function calculateLeaveDays(Carbon $startDate, Carbon $endDate, bool $isHalfDay = false): float
    {
        if ($isHalfDay) {
            return 0.5;
        }

        $days = 0.0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Exclude Saturdays and Sundays
            if (!$current->isWeekend()) {
                $days += 1.0;
            }
            $current->addDay();
        }

        return $days;
    }

    /**
     * Check if the employee has sufficient leave balance for the current year.
     */
    public function checkBalance(EmployeeProfile $employee, int $leaveTypeId, float $days): bool
    {
        $currentYear = Carbon::now()->year;

        $balance = HrLeaveBalance::where('employee_profile_id', $employee->id)
            ->where('hr_leave_type_id', $leaveTypeId)
            ->where('year', $currentYear)
            ->first();

        if (!$balance) {
            return false;
        }

        return $balance->available >= $days;
    }

    /**
     * Deduct days from the employee's leave balance for the current year.
     */
    public function deductBalance(EmployeeProfile $employee, int $leaveTypeId, float $days): void
    {
        $currentYear = Carbon::now()->year;

        $balance = HrLeaveBalance::where('employee_profile_id', $employee->id)
            ->where('hr_leave_type_id', $leaveTypeId)
            ->where('year', $currentYear)
            ->firstOrFail();

        $balance->update([
            'used' => $balance->used + $days,
            'available' => $balance->available - $days,
        ]);
    }

    /**
     * Restore days to balance (when leave is cancelled).
     */
    public function restoreBalance(EmployeeProfile $employee, int $leaveTypeId, float $days): void
    {
        $currentYear = Carbon::now()->year;

        $balance = HrLeaveBalance::where('employee_profile_id', $employee->id)
            ->where('hr_leave_type_id', $leaveTypeId)
            ->where('year', $currentYear)
            ->firstOrFail();

        $balance->update([
            'used' => max(0, $balance->used - $days),
            'available' => $balance->available + $days,
        ]);
    }

    /**
     * Run monthly accrual for all active employees in the organization.
     *
     * For each leave type with accrual_type='monthly', add accrual_count
     * to each employee's balance. Returns the count of employees processed.
     */
    public function accrueLeaves(Organization $org): int
    {
        $currentYear = Carbon::now()->year;

        // Get all leave types with monthly accrual
        $leaveTypes = HrLeaveType::where('organization_id', $org->id)
            ->where('is_active', true)
            ->where('accrual_type', 'monthly')
            ->get();

        if ($leaveTypes->isEmpty()) {
            return 0;
        }

        // Get all active employees
        $employees = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->get();

        $processedCount = 0;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $balance = HrLeaveBalance::firstOrCreate(
                    [
                        'organization_id' => $org->id,
                        'employee_profile_id' => $employee->id,
                        'hr_leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                    ],
                    [
                        'opening_balance' => 0,
                        'accrued' => 0,
                        'used' => 0,
                        'adjusted' => 0,
                        'carried_forward' => 0,
                        'encashed' => 0,
                        'available' => 0,
                    ]
                );

                // Add accrual amount, but respect max_days_per_year
                $maxDays = $leaveType->max_days_per_year;
                $totalAfterAccrual = $balance->accrued + $leaveType->accrual_count;

                // Cap accrual at max days per year (minus opening/carried forward)
                $totalAllocated = $balance->opening_balance + $balance->carried_forward + $totalAfterAccrual;
                if ($maxDays && $totalAllocated > $maxDays) {
                    $accrualAmount = max(0, $leaveType->accrual_count - ($totalAllocated - $maxDays));
                } else {
                    $accrualAmount = $leaveType->accrual_count;
                }

                if ($accrualAmount > 0) {
                    $balance->update([
                        'accrued' => $balance->accrued + $accrualAmount,
                        'available' => $balance->available + $accrualAmount,
                    ]);
                }
            }

            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * Carry forward unused leave balances to the new year, respecting
     * carry_forward_limit per leave type. Creates new year's balance records.
     * Returns the count of employees processed.
     */
    public function carryForwardLeaves(Organization $org, int $fromYear, int $toYear): int
    {
        // Get all leave types that allow carry forward
        $leaveTypes = HrLeaveType::where('organization_id', $org->id)
            ->where('is_active', true)
            ->whereNotNull('carry_forward_limit')
            ->where('carry_forward_limit', '>', 0)
            ->get();

        if ($leaveTypes->isEmpty()) {
            return 0;
        }

        $processedCount = 0;
        $processedEmployees = [];

        foreach ($leaveTypes as $leaveType) {
            // Get all balances for this leave type from the source year
            $balances = HrLeaveBalance::where('organization_id', $org->id)
                ->where('hr_leave_type_id', $leaveType->id)
                ->where('year', $fromYear)
                ->where('available', '>', 0)
                ->get();

            foreach ($balances as $balance) {
                // Calculate carry forward amount (capped at limit)
                $carryForwardAmount = min($balance->available, $leaveType->carry_forward_limit);

                if ($carryForwardAmount <= 0) {
                    continue;
                }

                // Create or update the new year's balance
                $newBalance = HrLeaveBalance::firstOrCreate(
                    [
                        'organization_id' => $org->id,
                        'employee_profile_id' => $balance->employee_profile_id,
                        'hr_leave_type_id' => $leaveType->id,
                        'year' => $toYear,
                    ],
                    [
                        'opening_balance' => 0,
                        'accrued' => 0,
                        'used' => 0,
                        'adjusted' => 0,
                        'carried_forward' => 0,
                        'encashed' => 0,
                        'available' => 0,
                    ]
                );

                $newBalance->update([
                    'carried_forward' => $carryForwardAmount,
                    'available' => $newBalance->available + $carryForwardAmount,
                ]);

                if (!in_array($balance->employee_profile_id, $processedEmployees)) {
                    $processedEmployees[] = $balance->employee_profile_id;
                    $processedCount++;
                }
            }
        }

        return $processedCount;
    }

    /**
     * Mark expired comp-offs (past expires_on and status='approved') as expired.
     * Returns the count of records updated.
     */
    public function expireCompOffs(Organization $org): int
    {
        $today = Carbon::today();

        return HrCompOff::where('organization_id', $org->id)
            ->where('status', 'approved')
            ->where('expires_on', '<', $today->toDateString())
            ->update(['status' => 'expired']);
    }
}
