<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use App\Models\HrCompOff;

class HrExpireCompOffs extends Command
{
    protected $signature = 'hr:expire-comp-offs {--org= : Organization ID (optional, run for all if omitted)}';
    protected $description = 'Expire comp-off leaves that have passed their expiry date';

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

        $totalExpired = 0;

        foreach ($organizations as $org) {
            $expired = $this->expireForOrganization($org);
            $totalExpired += $expired;
            $this->info("Organization [{$org->name}]: {$expired} comp-off(s) expired.");
        }

        $this->newLine();
        $this->info("Done. Total comp-offs expired: {$totalExpired}");

        return Command::SUCCESS;
    }

    private function expireForOrganization(Organization $org): int
    {
        $today = now()->toDateString();

        // Find all approved comp-offs that have passed their expiry date
        $expiredCount = HrCompOff::where('organization_id', $org->id)
            ->where('status', 'approved')
            ->whereNotNull('expires_on')
            ->where('expires_on', '<', $today)
            ->count();

        if ($expiredCount === 0) {
            $this->line("  No expired comp-offs for [{$org->name}]. Skipping.");
            return 0;
        }

        // Update all matching comp-offs to expired status
        HrCompOff::where('organization_id', $org->id)
            ->where('status', 'approved')
            ->whereNotNull('expires_on')
            ->where('expires_on', '<', $today)
            ->update(['status' => 'expired']);

        return $expiredCount;
    }
}
