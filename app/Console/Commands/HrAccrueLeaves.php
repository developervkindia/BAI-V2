<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use App\Models\HrLeaveType;
use App\Models\HrLeaveBalance;
use App\Models\EmployeeProfile;

class HrAccrueLeaves extends Command
{
    protected $signature = 'hr:accrue-leaves {--org= : Organization ID (optional, run for all if omitted)}';
    protected $description = 'Accrue monthly leave balances for all active employees';

    public function handle(): int
    {
        $orgId = $this->option('org');
        $organizations = $orgId
            ? Organization::where('id', $orgId)->active()->get()
            : Organization::active()->get();

        if ($organizations->isEmpty()) {
            $this->warn('No active organizations found.');
            return Command::FAILURE;
        }

        $totalProcessed = 0;

        foreach ($organizations as $org) {
            $processed = $this->accrueForOrganization($org);
            $totalProcessed += $processed;
            $this->info("Organization [{$org->name}]: {$processed} employee balance(s) accrued.");
        }

        $this->newLine();
        $this->info("Done. Total employee balances accrued: {$totalProcessed}");

        return Command::SUCCESS;
    }

    private function accrueForOrganization(Organization $org): int
    {
        $currentYear = now()->year;
        $processed = 0;

        // Get all active leave types with monthly accrual for this org
        $leaveTypes = HrLeaveType::where('organization_id', $org->id)
            ->where('is_active', true)
            ->where('accrual_type', 'monthly')
            ->where('accrual_count', '>', 0)
            ->get();

        if ($leaveTypes->isEmpty()) {
            $this->line("  No monthly-accrual leave types for [{$org->name}]. Skipping.");
            return 0;
        }

        // Get all active employees in this org
        $employees = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->get();

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                // Find or create the balance record for this year
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

                $accrualAmount = $leaveType->accrual_count;

                // Cap the accrual so total doesn't exceed max_days_per_year
                if ($leaveType->max_days_per_year) {
                    $maxRemaining = $leaveType->max_days_per_year - $balance->accrued - $balance->opening_balance - $balance->carried_forward;
                    $accrualAmount = min($accrualAmount, max(0, $maxRemaining));
                }

                if ($accrualAmount <= 0) {
                    continue;
                }

                $balance->increment('accrued', $accrualAmount);
                $balance->increment('available', $accrualAmount);

                $processed++;
            }
        }

        return $processed;
    }
}
