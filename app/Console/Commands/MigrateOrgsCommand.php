<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Console\Command;

class MigrateOrgsCommand extends Command
{
    protected $signature   = 'ecosystem:migrate-orgs';
    protected $description = 'Create organizations for existing workspace owners and link their workspaces';

    public function __construct(protected OrganizationService $orgService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $users = User::has('ownedWorkspaces')->get();

        $this->info("Found {$users->count()} workspace owner(s) to migrate...");

        foreach ($users as $user) {
            if ($user->allOrganizations()->isEmpty()) {
                $org = $this->orgService->createForUser($user, [
                    'name' => $user->name . "'s Organization",
                ]);

                $count = $user->ownedWorkspaces()->update(['organization_id' => $org->id]);

                $this->line("  ✓ {$user->email} → org \"{$org->name}\" ({$count} workspace(s) linked)");
            } else {
                $this->line("  – {$user->email} already has an organization, skipping.");
            }
        }

        $this->info('Done.');
    }
}
