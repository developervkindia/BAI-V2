<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use App\Models\HrLeaveType;
use App\Models\HrLeaveBalance;

class HrCarryForwardLeaves extends Command
{
    protected $signature = 'hr:carry-forward-leaves
        {--org= : Organization ID (optional, run for all if omitted)}
        {--from-year= : Year to carry from (default: previous year)}
        {--to-year= : Year to carry to (default: current year)}';
    protected $description = 'Carry forward unused leave balances to the new year';

    public function handle(): int
    {
        $orgId = $this->option('org');
        $fromYear = $this->option('from-year') ?: (now()->year - 1);
        $toYear = $this->option('to-year') ?: now()->year;

        if ($fromYear >= $toYear) {
            $this->error("from-year ({$fromYear}) must be less than to-year ({$toYear}).");
            return Command::FAILURE;
        }

        $organizations = $orgId
            ? Organization::where('id', $orgId)->active()->get()
            : Organization::active()->get();

        if ($organizations->isEmpty()) {
            $this->warn('No active organizations found.');
            return Command::FAILURE;
        }

        $totalProcessed = 0;

        foreach ($organizations as $org) {
            $processed = $this->carryForwardForOrganization($org, (int) $fromYear, (int) $toYear);
            $totalProcessed += $processed;
            $this->info("Organization [{$org->name}]: {$processed} balance(s) carried forward from {$fromYear} to {$toYear}.");
        }

        $this->newLine();
        $this->info("Done. Total balances carried forward: {$totalProcessed}");

        return Command::SUCCESS;
    }

    private function carryForwardForOrganization(Organization $org, int $fromYear, int $toYear): int
    {
        $processed = 0;

        // Get all active leave types that allow carry forward for this org
        $leaveTypes = HrLeaveType::where('organization_id', $org->id)
            ->where('is_active', true)
            ->where('carry_forward_limit', '>', 0)
            ->get();

        if ($leaveTypes->isEmpty()) {
            $this->line("  No carry-forward leave types for [{$org->name}]. Skipping.");
            return 0;
        }

        // Get all balances for the source year in this org
        $sourceBalances = HrLeaveBalance::where('organization_id', $org->id)
            ->where('year', $fromYear)
            ->whereIn('hr_leave_type_id', $leaveTypes->pluck('id'))
            ->get();

        foreach ($sourceBalances as $sourceBalance) {
            $leaveType = $leaveTypes->firstWhere('id', $sourceBalance->hr_leave_type_id);

            if (!$leaveType) {
                continue;
            }

            // Calculate how much can be carried forward
            $unusedBalance = $sourceBalance->available;

            if ($unusedBalance <= 0) {
                continue;
            }

            // Apply carry forward limit
            $carryAmount = min($unusedBalance, $leaveType->carry_forward_limit);

            if ($carryAmount <= 0) {
                continue;
            }

            // Find or create the target year balance record
            $targetBalance = HrLeaveBalance::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'employee_profile_id' => $sourceBalance->employee_profile_id,
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

            // Add carry-forward amount to the target year
            $targetBalance->increment('carried_forward', $carryAmount);
            $targetBalance->increment('available', $carryAmount);

            $processed++;
        }

        return $processed;
    }
}
