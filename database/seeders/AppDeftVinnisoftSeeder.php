<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Milestone;
use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Sprint;
use App\Models\TaskList;
use App\Models\User;
use App\Services\OrgMemberOnboardingService;
use App\Services\ProductAccessService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds AppDeft and Vinnisoft organizations, users from user-import-template.csv,
 * and one demo project per org (task lists, milestones, sprints, tasks).
 */
class AppDeftVinnisoftSeeder extends Seeder
{
    private const CSV_PATH = 'user-import-template.csv';

    private const RAKESH_EMAIL = 'hello@appdeft.ai';

    /** Must match user-import-template.csv (Vasudev Arora row). */
    private const VASUDEV_EMAIL = 'vinnisoft.vis@gmail.com';

    private const RAKESH_DEFAULT_PASSWORD = 'rakesh@1234';

    private const VASUDEV_DEFAULT_PASSWORD = 'vasudev@1234';

    public function run(): void
    {
        $csvPath = base_path(self::CSV_PATH);
        if (! is_readable($csvPath)) {
            $this->command?->error('AppDeftVinnisoftSeeder: missing '.self::CSV_PATH.' at project root.');

            return;
        }

        $rows = $this->readCsv($csvPath);
        if ($rows->isEmpty()) {
            $this->command?->warn('AppDeftVinnisoftSeeder: no data rows in CSV.');

            return;
        }

        $productAccess = app(ProductAccessService::class);
        $onboarding = app(OrgMemberOnboardingService::class);

        foreach ($rows as $row) {
            $email = $this->normalizeEmail($row['email']);
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->command?->warn("AppDeftVinnisoftSeeder: skip invalid email for \"{$row['name']}\".");

                continue;
            }

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $row['name'],
                    'password' => Hash::make($row['password']),
                ]
            );
        }

        // Owners must exist even if CSV omits them, uses different casing in DB, or deployed CSV differs.
        $rakesh = $this->ensureOrgOwner(
            self::RAKESH_EMAIL,
            'Rakesh Kumar',
            self::RAKESH_DEFAULT_PASSWORD
        );
        $vasudev = $this->ensureOrgOwner(
            self::VASUDEV_EMAIL,
            'Vasudev Arora',
            self::VASUDEV_DEFAULT_PASSWORD
        );

        $appdeft = $this->ensureOrganization('AppDeft', $rakesh, $productAccess, $onboarding);
        $vinnisoft = $this->ensureOrganization('Vinnisoft', $vasudev, $productAccess, $onboarding);

        foreach ($rows as $row) {
            $email = $this->normalizeEmail($row['email']);
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $orgKey = $this->resolveOrgKey($email);
            if ($orgKey === null) {
                $this->command?->warn("AppDeftVinnisoftSeeder: no org match for {$email}; skipped.");

                continue;
            }

            $org = $orgKey === 'appdeft' ? $appdeft : $vinnisoft;
            $user = $this->findUserByEmailInsensitive($email);
            if (! $user) {
                continue;
            }

            $isOrgOwner = ($orgKey === 'appdeft' && $user->id === $rakesh->id)
                || ($orgKey === 'vinnisoft' && $user->id === $vasudev->id);

            if ($org->members()->where('user_id', $user->id)->exists()) {
                continue;
            }

            $org->members()->attach($user->id, ['role' => $isOrgOwner ? 'owner' : 'member']);
            if (! $isOrgOwner) {
                $onboarding->provisionMember($org, $user, 'member');
            }
        }

        // Enterprise subscriptions + full RBAC for AppDeft / Vinnisoft (plan unlocks + all permissions on owner/admin).
        foreach ([$appdeft, $vinnisoft] as $org) {
            $productAccess->provisionEnterpriseForOrg($org);
            PermissionSeeder::seedRolesForOrg($org);
        }
        $onboarding->provisionMember($appdeft, $rakesh, 'owner');
        $onboarding->provisionMember($vinnisoft, $vasudev, 'owner');

        $appdeftMembers = $appdeft->members()->get();
        if (! $appdeftMembers->contains('id', $rakesh->id)) {
            $appdeftMembers->push($rakesh);
        }
        (new AppDeftRichProjectsSeeder)->runFor(
            $appdeft,
            $rakesh,
            $appdeftMembers->unique('id')->values()
        );

        $vinnisoftMembers = $vinnisoft->members()->get();
        if (! $vinnisoftMembers->contains('id', $vasudev->id)) {
            $vinnisoftMembers->push($vasudev);
        }
        $this->seedDemoProject(
            $vinnisoft,
            $vasudev,
            $vinnisoftMembers->unique('id')->values(),
            'Release — Mobile Platform 2.0',
            'Sprint-based delivery for the Vinnisoft mobile platform milestone release.',
            '#0D9488'
        );

        $this->command?->info('AppDeftVinnisoftSeeder finished: 2 orgs, CSV users; AppDeft has rich fixed + billing projects; Vinnisoft has one demo project.');
    }

    private function readCsv(string $path): Collection
    {
        $lines = collect();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return $lines;
        }

        $header = fgetcsv($handle);
        if (! is_array($header) || $header === false) {
            fclose($handle);

            return $lines;
        }

        $header = array_map(static fn ($h) => strtolower(trim((string) $h)), $header);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                continue;
            }
            $lines->push([
                'name' => trim((string) $row[0]),
                'email' => trim((string) $row[1]),
                'role' => trim((string) $row[2]),
                'password' => trim((string) $row[3]) ?: 'password',
            ]);
        }

        fclose($handle);

        return $lines;
    }

    private function normalizeEmail(string $raw): string
    {
        $e = str_replace(["\t", "\r", '%09'], '', $raw);

        return strtolower(trim($e));
    }

    private function findUserByEmailInsensitive(string $email): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();
    }

    private function ensureOrgOwner(string $email, string $name, string $defaultPassword): User
    {
        $normalized = strtolower(trim($email));
        $existing = $this->findUserByEmailInsensitive($normalized);
        if ($existing) {
            return $existing;
        }

        $this->command?->warn("AppDeftVinnisoftSeeder: creating org owner {$normalized} (not in CSV or DB yet).");

        return User::create([
            'email' => $normalized,
            'name' => $name,
            'password' => Hash::make($defaultPassword),
        ]);
    }

    private function resolveOrgKey(string $email): ?string
    {
        if (str_contains($email, 'appdeft')) {
            return 'appdeft';
        }
        if (str_contains($email, 'vinnisoft')) {
            return 'vinnisoft';
        }

        return null;
    }

    private function ensureOrganization(
        string $name,
        User $owner,
        ProductAccessService $productAccess,
        OrgMemberOnboardingService $onboarding,
    ): Organization {
        $org = Organization::where('name', $name)->first();

        if (! $org) {
            $org = Organization::create([
                'name' => $name,
                'description' => "{$name} organization (seeded from user-import-template.csv).",
                'owner_id' => $owner->id,
            ]);
            $org->members()->attach($owner->id, ['role' => 'owner']);
            $onboarding->provisionMember($org, $owner, 'owner');
        }

        $productAccess->provisionFreeSmartBoard($org);

        return $org->fresh();
    }

    private function seedDemoProject(
        Organization $org,
        User $projectOwner,
        Collection $team,
        string $name,
        string $description,
        string $color,
    ): void {
        $client = Client::firstOrCreate(
            ['organization_id' => $org->id, 'email' => 'demo-stakeholder@'.Str::slug($org->name).'.local'],
            [
                'name' => 'Demo stakeholder',
                'company' => $org->name.' stakeholder',
                'phone' => '+91 0000000000',
                'timezone' => 'Asia/Kolkata',
                'notes' => 'Seeded client for '.$org->name,
            ]
        );

        $project = Project::firstOrCreate(
            ['organization_id' => $org->id, 'name' => $name],
            [
                'owner_id' => $projectOwner->id,
                'client_id' => $client->id,
                'description' => $description,
                'status' => 'in_progress',
                'priority' => 'high',
                'color' => $color,
                'start_date' => now()->subWeeks(4)->toDateString(),
                'end_date' => now()->addWeeks(8)->toDateString(),
                'visibility' => 'organization',
                'project_type' => 'fixed',
            ]
        );

        foreach ($team as $user) {
            if (! $project->members()->where('user_id', $user->id)->exists()) {
                $project->members()->attach($user->id, [
                    'role' => $user->id === $projectOwner->id ? 'manager' : 'member',
                ]);
            }
        }

        if ($project->tasks()->count() >= 3) {
            return;
        }

        $assignees = $team->values();
        if ($assignees->isEmpty()) {
            $assignees = collect([$projectOwner]);
        }

        $statuses = $project->statuses;
        $open = $statuses->firstWhere('slug', 'open');
        $inProgress = $statuses->firstWhere('slug', 'in_progress');
        $completed = $statuses->firstWhere('slug', 'completed');

        $lists = [];
        foreach (['Backlog', 'Current sprint', 'QA & release'] as $i => $listName) {
            $lists[] = TaskList::firstOrCreate(
                ['project_id' => $project->id, 'name' => $listName],
                ['position' => ($i + 1) * 1000]
            );
        }

        $m1 = Milestone::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'MVP scope'],
            [
                'description' => 'Core deliverables agreed with stakeholders',
                'due_date' => now()->addWeeks(2)->toDateString(),
                'status' => 'open',
            ]
        );
        $m2 = Milestone::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Production launch'],
            [
                'description' => 'Go-live checklist and handover',
                'due_date' => now()->addWeeks(6)->toDateString(),
                'status' => 'open',
            ]
        );

        $sprint1 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 1 — Foundation'],
            [
                'start_date' => now()->subWeeks(4)->toDateString(),
                'end_date' => now()->subWeeks(2)->toDateString(),
                'status' => 'completed',
            ]
        );
        $sprint2 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 2 — Build'],
            [
                'start_date' => now()->subWeeks(2)->toDateString(),
                'end_date' => now()->toDateString(),
                'status' => 'active',
            ]
        );
        $sprint3 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 3 — Hardening'],
            [
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addWeeks(2)->toDateString(),
                'status' => 'planning',
            ]
        );

        $pick = static function (int $i) use ($assignees) {
            return $assignees[$i % $assignees->count()];
        };

        $taskDefs = [
            [$lists[0]->id, 'Define requirements & acceptance criteria', $completed, $m1, 0, true, 8],
            [$lists[0]->id, 'Contract review with stakeholders', $inProgress, $m1, 1, false, 6],
            [$lists[1]->id, 'Implement core user flows', $inProgress, $m1, 2, false, 24],
            [$lists[1]->id, 'Authentication & authorization', $open, $m1, 3, false, 16],
            [$lists[1]->id, 'Notifications & messaging hooks', $open, $m2, 4, false, 12],
            [$lists[2]->id, 'Regression testing', $open, $m2, 5, false, 14],
            [$lists[2]->id, 'Performance tuning', $open, $m2, 6, false, 10],
            [$lists[2]->id, 'Deployment runbook', $open, $m2, 0, false, 4],
        ];

        $tasks = collect();
        foreach ($taskDefs as $i => $td) {
            [$listId, $title, $status, $milestone, $assigneeIdx, $isDone, $hours] = $td;
            $assignee = $pick($assigneeIdx);
            $priorities = ['medium', 'high', 'high', 'medium', 'low', 'medium', 'high', 'low'];
            $points = [3, 5, 8, 5, 3, 8, 5, 2];
            $task = ProjectTask::create([
                'project_id' => $project->id,
                'task_list_id' => $listId,
                'title' => $title,
                'description' => 'Seeded task: '.$title,
                'issue_type' => 'task',
                'priority' => $priorities[$i],
                'project_status_id' => $status?->id,
                'status' => $status?->slug ?? 'open',
                'milestone_id' => $milestone->id,
                'assignee_id' => $assignee->id,
                'estimated_hours' => $hours,
                'is_completed' => $isDone,
                'completed_at' => $isDone ? now()->subDays(3) : null,
                'start_date' => now()->subWeeks(3)->toDateString(),
                'due_date' => now()->addWeeks(1 + ($i % 4))->toDateString(),
                'position' => ($i + 1) * 1000,
                'story_points' => $points[$i],
            ]);
            $tasks->push($task);
        }

        $sprint1->tasks()->syncWithoutDetaching($tasks->slice(0, 3)->pluck('id')->all());
        $sprint2->tasks()->syncWithoutDetaching($tasks->slice(3, 3)->pluck('id')->all());
        $sprint3->tasks()->syncWithoutDetaching($tasks->slice(6, 2)->pluck('id')->all());
    }
}
