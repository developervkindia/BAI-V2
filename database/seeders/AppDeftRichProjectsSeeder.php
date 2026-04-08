<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Milestone;
use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectBillingEntry;
use App\Models\ProjectBillingWeek;
use App\Models\ProjectComment;
use App\Models\ProjectCustomField;
use App\Models\ProjectFolder;
use App\Models\ProjectLabel;
use App\Models\ProjectMessage;
use App\Models\ProjectSavedView;
use App\Models\ProjectScopeChange;
use App\Models\ProjectStatus;
use App\Models\ProjectTask;
use App\Models\ProjectTaskChecklist;
use App\Models\ProjectTaskChecklistItem;
use App\Models\ProjectTaskCustomFieldValue;
use App\Models\ProjectTaskLink;
use App\Models\ProjectTimeLog;
use App\Models\ProjectWeeklyUpdate;
use App\Models\RecurringTaskPattern;
use App\Models\Sprint;
use App\Models\TaskList;
use App\Models\User;
use App\Models\UserCapacity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Rich fixed + billing projects for AppDeft (~3 months of realistic history).
 * Invoked from AppDeftVinnisoftSeeder; can run standalone if AppDeft org exists.
 */
class AppDeftRichProjectsSeeder extends Seeder
{
    public const FIXED_PROJECT_NAME = 'B2B Commerce Portal — Fixed Delivery';

    public const BILLING_PROJECT_NAME = 'Meridian Analytics Platform — T&M Engagement';

    public function run(): void
    {
        $org = Organization::where('name', 'AppDeft')->first();
        if (! $org) {
            $this->command?->warn('AppDeftRichProjectsSeeder: AppDeft organization not found. Run AppDeftVinnisoftSeeder first.');

            return;
        }

        $owner = User::find($org->owner_id);
        if (! $owner) {
            $this->command?->error('AppDeftRichProjectsSeeder: AppDeft has no owner.');

            return;
        }

        $team = $org->members()->get();
        $this->runFor($org, $owner, $team);
    }

    public function runFor(Organization $org, User $owner, Collection $team): void
    {
        $users = $this->normalizeTeam($owner, $team);
        $ref = now();
        $projectStarted = $ref->copy()->subMonths(3);

        $clients = $this->clientsForAppDeft($org);

        $this->seedAppDeftFixedProject($org, $users, $clients[0], $ref, $projectStarted);
        $this->seedAppDeftBillingProject($org, $users, $clients[1], $ref, $projectStarted);

        $this->command?->info('AppDeftRichProjectsSeeder: fixed + billing projects seeded with full history.');
    }

    /**
     * @return array{0: Client, 1: Client}
     */
    private function clientsForAppDeft(Organization $org): array
    {
        $fixed = Client::firstOrCreate(
            ['organization_id' => $org->id, 'email' => 'procurement@nishka-retail.demo'],
            [
                'name' => 'Kavita Menon',
                'company' => 'Nishka Retail Pvt Ltd',
                'phone' => '+91 99887 76655',
                'timezone' => 'Asia/Kolkata',
                'notes' => 'Fixed-bid ecommerce modernization; AppDeft primary delivery partner since '.$this->fmtMonthYear(now()->subMonths(3)),
            ]
        );

        $billing = Client::firstOrCreate(
            ['organization_id' => $org->id, 'email' => 'engineering@meridian-analytics.demo'],
            [
                'name' => 'Jordan Ellis',
                'company' => 'Meridian Analytics Inc',
                'phone' => '+1 415 555 0192',
                'timezone' => 'America/Los_Angeles',
                'notes' => 'Time & materials engagement for embedded analytics squad; weekly billing, sprint demos.',
            ]
        );

        return [$fixed, $billing];
    }

    private function fmtMonthYear(\DateTimeInterface $d): string
    {
        return $d->format('M Y');
    }

    /**
     * @return list<User>
     */
    private function normalizeTeam(User $owner, Collection $team): array
    {
        $ordered = collect([$owner]);
        foreach ($team as $u) {
            if ($u->id !== $owner->id) {
                $ordered->push($u);
            }
        }

        return $ordered->unique('id')->values()->all();
    }

    /**
     * @param  list<User>  $users
     */
    private function u(array $users, int $index): User
    {
        $n = count($users);
        if ($n === 0) {
            throw new \RuntimeException('AppDeftRichProjectsSeeder: team is empty.');
        }

        return $users[$index % $n];
    }

    /**
     * @param  list<User>  $users
     */
    private function seedAppDeftFixedProject(
        Organization $org,
        array $users,
        Client $client,
        Carbon $ref,
        Carbon $projectStarted,
    ): void {
        $project = Project::firstOrCreate(
            ['organization_id' => $org->id, 'name' => self::FIXED_PROJECT_NAME],
            [
                'owner_id' => $users[0]->id,
                'client_id' => $client->id,
                'description' => 'Fixed-scope programme for Nishka Retail: unified B2B catalog, contract pricing, PunchOut, and fulfillment handoff. Running since '.$projectStarted->format('M j, Y').' with staged milestones toward hard go-live.',
                'status' => 'in_progress',
                'priority' => 'high',
                'color' => '#6366F1',
                'start_date' => $projectStarted->toDateString(),
                'end_date' => $ref->copy()->addMonths(2)->toDateString(),
                'visibility' => 'organization',
                'project_type' => 'fixed',
                'budget' => 520000,
                'hourly_rate' => 2650,
                'srs_url' => 'https://docs.google.com/document/d/appdeft-nishka-srs',
                'design_url' => 'https://figma.com/file/nishka-b2b-portal',
                'design_status' => 'approved',
                'design_approved_by' => $users[0]->id,
                'design_approved_at' => $ref->copy()->subWeeks(10),
                'design_feedback' => 'Signed off WCAG 2.1 AA palette and component spacing; procurement requested PunchOut addendum.',
            ]
        );

        foreach ($users as $i => $user) {
            if (! $project->members()->where('user_id', $user->id)->exists()) {
                $pmIndex = max(0, min(6, count($users) - 1));
                $role = $i === 0 ? 'manager' : ($i === $pmIndex ? 'manager' : 'member');
                $project->members()->attach($user->id, ['role' => $role]);
            }
        }

        $statuses = $project->statuses;
        $inReview = ProjectStatus::firstOrCreate(
            ['project_id' => $project->id, 'slug' => 'in_review'],
            ['name' => 'In Review', 'color' => '#8B5CF6', 'position' => 2.5, 'is_completed_state' => false, 'is_default' => false]
        );
        $qaStatus = ProjectStatus::firstOrCreate(
            ['project_id' => $project->id, 'slug' => 'qa_testing'],
            ['name' => 'QA Testing', 'color' => '#F59E0B', 'position' => 2.8, 'is_completed_state' => false, 'is_default' => false]
        );

        $openStatus = $statuses->firstWhere('slug', 'open');
        $inProgressStatus = $statuses->firstWhere('slug', 'in_progress');
        $completedStatus = $statuses->firstWhere('slug', 'completed');
        $deferredStatus = $statuses->firstWhere('slug', 'deferred');

        $labels = [];
        foreach ([
            ['Frontend', '#3B82F6'], ['Backend', '#10B981'], ['Bug', '#EF4444'],
            ['Enhancement', '#8B5CF6'], ['Urgent', '#F43F5E'], ['Design', '#EC4899'],
            ['Documentation', '#6B7280'], ['Performance', '#F59E0B'],
        ] as [$name, $color]) {
            $labels[] = ProjectLabel::firstOrCreate(
                ['project_id' => $project->id, 'name' => $name],
                ['color' => $color]
            );
        }

        $envField = ProjectCustomField::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Environment'],
            ['type' => 'dropdown', 'options' => ['Development', 'Staging', 'Production'], 'position' => 1]
        );
        ProjectCustomField::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'PR Link'],
            ['type' => 'url', 'position' => 2]
        );
        $reviewed = ProjectCustomField::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Code Reviewed'],
            ['type' => 'checkbox', 'position' => 3]
        );
        ProjectCustomField::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Complexity Score'],
            ['type' => 'number', 'position' => 4]
        );

        $m1 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Discovery & design'], ['description' => 'Journey maps, design system, PunchOut wireframes', 'due_date' => $ref->copy()->subMonths(2)->toDateString(), 'status' => 'completed']);
        $m2 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Catalog & contract pricing'], ['description' => 'SKU ingestion, customer-specific price lists', 'due_date' => $ref->copy()->subWeeks(2)->toDateString(), 'status' => 'open']);
        $m3 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'PunchOut & OCI'], ['description' => 'Coupa / Ariba adapters', 'due_date' => $ref->copy()->addWeeks(4)->toDateString(), 'status' => 'open']);
        $m4 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Go-live & hypercare'], ['description' => 'Cutover, monitoring, training', 'due_date' => $ref->copy()->addMonths(2)->toDateString(), 'status' => 'open']);

        $sections = [];
        foreach (['Product Catalog', 'Shopping Cart & Checkout', 'User Authentication', 'Admin Dashboard', 'API & Integrations', 'DevOps & Deployment'] as $i => $name) {
            $sections[] = TaskList::firstOrCreate(
                ['project_id' => $project->id, 'name' => $name],
                ['position' => ($i + 1) * 1000]
            );
        }

        $sprint1 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 1 — Foundation (weeks 1–2)'],
            ['start_date' => $projectStarted->copy()->toDateString(), 'end_date' => $projectStarted->copy()->addWeeks(2)->toDateString(), 'status' => 'completed']
        );
        $sprint2 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 2 — Core commerce (weeks 3–5)'],
            ['start_date' => $projectStarted->copy()->addWeeks(2)->toDateString(), 'end_date' => $projectStarted->copy()->addWeeks(5)->toDateString(), 'status' => 'completed']
        );
        $sprint3 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 3 — Integrations (weeks 6–9)'],
            ['start_date' => $projectStarted->copy()->addWeeks(5)->toDateString(), 'end_date' => $projectStarted->copy()->addWeeks(9)->toDateString(), 'status' => 'completed']
        );
        $sprint4 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 4 — Hardening & launch prep'],
            ['start_date' => $projectStarted->copy()->addWeeks(9)->toDateString(), 'end_date' => $ref->copy()->addWeeks(2)->toDateString(), 'status' => 'active']
        );

        $skipTasks = $project->tasks()->count() > 5;
        if ($skipTasks) {
            $this->command?->info('  AppDeft fixed project already has tasks, skipping task creation.');

            return;
        }

        $taskDefs = [
            [$sections[0]->id, 'Design product listing page', 'task', 'critical', $completedStatus, $m1, 3, 8, true, $ref->copy()->subWeeks(11), $ref->copy()->subWeeks(9)],
            [$sections[0]->id, 'Implement product grid with filters', 'task', 'high', $completedStatus, $m2, 1, 16, true, $ref->copy()->subWeeks(10), $ref->copy()->subWeeks(7)],
            [$sections[0]->id, 'Build product detail page', 'task', 'high', $inProgressStatus, $m2, 1, 12, false, $ref->copy()->subWeeks(8), $ref->copy()->addDays(2)],
            [$sections[0]->id, 'Add product search with Algolia', 'task', 'medium', $inProgressStatus, $m2, 2, 10, false, $ref->copy()->subWeeks(7), $ref->copy()->addDays(5)],
            [$sections[0]->id, 'Product image zoom and gallery', 'story', 'medium', $openStatus, $m2, 1, 6, false, $ref->copy()->subWeeks(6), $ref->copy()->addWeeks(1)],
            [$sections[0]->id, 'Product reviews & ratings system', 'story', 'low', $openStatus, $m3, 2, 14, false, null, $ref->copy()->addWeeks(3)],
            [$sections[0]->id, 'Fix product price formatting bug', 'bug', 'high', $completedStatus, $m2, 1, 2, true, $ref->copy()->subWeeks(4), $ref->copy()->subWeeks(3)],
            [$sections[0]->id, 'Product comparison feature', 'epic', 'low', $deferredStatus, null, 2, 20, false, null, $ref->copy()->addWeeks(6)],

            [$sections[1]->id, 'Build shopping cart component', 'task', 'critical', $completedStatus, $m2, 1, 12, true, $ref->copy()->subWeeks(9), $ref->copy()->subWeeks(7)],
            [$sections[1]->id, 'Implement cart persistence (localStorage)', 'task', 'high', $completedStatus, $m2, 1, 4, true, $ref->copy()->subWeeks(8), $ref->copy()->subWeeks(7)],
            [$sections[1]->id, 'Multi-step checkout flow', 'task', 'critical', $inProgressStatus, $m2, 1, 16, false, $ref->copy()->subWeeks(7), $ref->copy()->addDays(3)],
            [$sections[1]->id, 'Address form with Google Places API', 'task', 'medium', $inReview, $m2, 2, 8, false, $ref->copy()->subWeeks(6), $ref->copy()->addDays(1)],
            [$sections[1]->id, 'Order confirmation email template', 'task', 'medium', $openStatus, $m3, 3, 4, false, null, $ref->copy()->addWeeks(2)],
            [$sections[1]->id, 'Cart quantity update not syncing', 'bug', 'critical', $qaStatus, $m2, 5, 3, false, $ref->copy()->subDays(3), $ref->copy()->addDays(1)],
            [$sections[1]->id, 'Apply coupon/promo code system', 'story', 'medium', $openStatus, $m3, 2, 10, false, null, $ref->copy()->addWeeks(3)],

            [$sections[2]->id, 'Login & Registration pages', 'task', 'high', $completedStatus, $m1, 2, 8, true, $ref->copy()->subWeeks(11), $ref->copy()->subWeeks(10)],
            [$sections[2]->id, 'OAuth (Google, Microsoft) integration', 'task', 'medium', $completedStatus, $m2, 2, 6, true, $ref->copy()->subWeeks(9), $ref->copy()->subWeeks(8)],
            [$sections[2]->id, 'Password reset flow', 'task', 'high', $completedStatus, $m2, 2, 4, true, $ref->copy()->subWeeks(9), $ref->copy()->subWeeks(8)],
            [$sections[2]->id, 'User profile & order history page', 'task', 'medium', $inProgressStatus, $m2, 1, 10, false, $ref->copy()->subWeeks(6), $ref->copy()->addDays(4)],
            [$sections[2]->id, 'Two-factor authentication', 'story', 'low', $openStatus, $m3, 2, 8, false, null, $ref->copy()->addWeeks(4)],

            [$sections[3]->id, 'Admin dashboard wireframes', 'task', 'high', $completedStatus, $m1, 3, 6, true, $ref->copy()->subWeeks(11), $ref->copy()->subWeeks(10)],
            [$sections[3]->id, 'Order management system', 'task', 'high', $inProgressStatus, $m2, 2, 16, false, $ref->copy()->subWeeks(7), $ref->copy()->addWeeks(1)],
            [$sections[3]->id, 'Product inventory management', 'task', 'medium', $openStatus, $m3, 2, 12, false, null, $ref->copy()->addWeeks(3)],
            [$sections[3]->id, 'Sales analytics dashboard', 'story', 'medium', $openStatus, $m4, 1, 14, false, null, $ref->copy()->addWeeks(5)],
            [$sections[3]->id, 'Customer management panel', 'task', 'low', $openStatus, $m4, 2, 8, false, null, $ref->copy()->addWeeks(6)],

            [$sections[4]->id, 'RESTful API architecture design', 'task', 'critical', $completedStatus, $m1, 2, 8, true, $ref->copy()->subWeeks(12), $ref->copy()->subWeeks(11)],
            [$sections[4]->id, 'Razorpay payment gateway integration', 'task', 'critical', $inProgressStatus, $m3, 2, 12, false, $ref->copy()->subWeeks(2), $ref->copy()->addWeeks(2)],
            [$sections[4]->id, 'Stripe international payments', 'task', 'high', $openStatus, $m3, 2, 10, false, null, $ref->copy()->addWeeks(3)],
            [$sections[4]->id, 'Shiprocket shipping API integration', 'task', 'medium', $openStatus, $m3, 4, 8, false, null, $ref->copy()->addWeeks(3)],
            [$sections[4]->id, 'SMS notification (MSG91) integration', 'task', 'low', $openStatus, $m4, 4, 6, false, null, $ref->copy()->addWeeks(5)],
            [$sections[4]->id, 'API rate limiting regression', 'bug', 'high', $inProgressStatus, $m2, 4, 4, false, $ref->copy()->subDays(5), $ref->copy()->addDays(2)],

            [$sections[5]->id, 'Set up CI/CD pipeline (GitHub Actions)', 'task', 'high', $completedStatus, $m1, 4, 6, true, $ref->copy()->subWeeks(11), $ref->copy()->subWeeks(10)],
            [$sections[5]->id, 'Configure staging environment on AWS', 'task', 'high', $completedStatus, $m2, 4, 8, true, $ref->copy()->subWeeks(9), $ref->copy()->subWeeks(8)],
            [$sections[5]->id, 'Set up Redis caching layer', 'task', 'medium', $inProgressStatus, $m2, 4, 6, false, $ref->copy()->subWeeks(3), $ref->copy()->addDays(3)],
            [$sections[5]->id, 'Production deployment checklist', 'task', 'medium', $openStatus, $m4, 4, 4, false, null, $ref->copy()->addWeeks(6)],
            [$sections[5]->id, 'Load testing & performance benchmark', 'task', 'high', $openStatus, $m4, 5, 8, false, null, $ref->copy()->addWeeks(5)],
            [$sections[5]->id, 'SSL certificate & domain configuration', 'task', 'high', $openStatus, $m4, 4, 2, false, null, $ref->copy()->addWeeks(7)],
        ];

        $allTasks = [];
        $labelIds = collect($labels)->pluck('id')->toArray();
        foreach ($taskDefs as $i => $td) {
            [$listId, $title, $issueType, $priority, $status, $milestone, $assigneeIx, $hours, $isDone, $start, $due] = $td;
            $assignee = $this->u($users, $assigneeIx);
            $task = ProjectTask::create([
                'project_id' => $project->id,
                'task_list_id' => $listId,
                'title' => $title,
                'description' => 'Work package: '.$title.' (Nishka programme).',
                'issue_type' => $issueType,
                'priority' => $priority,
                'project_status_id' => $status?->id,
                'status' => $status?->slug ?? 'open',
                'milestone_id' => $milestone?->id,
                'assignee_id' => $assignee->id,
                'estimated_hours' => $hours,
                'is_completed' => $isDone,
                'completed_at' => $isDone ? $ref->copy()->subDays(rand(5, 85)) : null,
                'start_date' => $start?->toDateString(),
                'due_date' => $due?->toDateString(),
                'position' => ($i + 1) * 1000,
                'story_points' => rand(1, 13),
            ]);
            $allTasks[] = $task;
            if (count($labelIds) > 0) {
                $pick = $labelIds;
                shuffle($pick);
                $task->labels()->sync(array_slice($pick, 0, min(2, count($pick))));
            }
        }

        foreach (array_slice($allTasks, 0, 5) as $parent) {
            $subCount = rand(2, 4);
            for ($s = 0; $s < $subCount; $s++) {
                ProjectTask::create([
                    'project_id' => $project->id,
                    'task_list_id' => $parent->task_list_id,
                    'parent_task_id' => $parent->id,
                    'title' => 'Subtask '.($s + 1).' — '.Str::limit($parent->title, 28),
                    'status' => $parent->status,
                    'project_status_id' => $parent->project_status_id,
                    'priority' => 'none',
                    'assignee_id' => $this->u($users, $s + 1)->id,
                    'position' => ($s + 1) * 1000,
                    'is_completed' => $parent->is_completed,
                    'completed_at' => $parent->completed_at,
                    'issue_type' => 'task',
                ]);
            }
        }

        $sprint1->tasks()->syncWithoutDetaching(collect($allTasks)->slice(0, 8)->pluck('id'));
        $sprint2->tasks()->syncWithoutDetaching(collect($allTasks)->slice(8, 8)->pluck('id'));
        $sprint3->tasks()->syncWithoutDetaching(collect($allTasks)->slice(16, 8)->pluck('id'));
        $sprint4->tasks()->syncWithoutDetaching(collect($allTasks)->slice(24, 10)->pluck('id'));

        if (count($allTasks) > 26) {
            ProjectTaskLink::firstOrCreate(['task_id' => $allTasks[26]->id, 'linked_task_id' => $allTasks[25]->id], ['type' => 'relates_to']);
        }
        ProjectTaskLink::firstOrCreate(['task_id' => $allTasks[2]->id, 'linked_task_id' => $allTasks[0]->id], ['type' => 'blocked_by']);
        ProjectTaskLink::firstOrCreate(['task_id' => $allTasks[10]->id, 'linked_task_id' => $allTasks[8]->id], ['type' => 'blocked_by']);

        $commentTexts = [
            'Initial PR opened — tagging reviewer.',
            'Design approved for this slice; proceeding to implementation.',
            'Blocked on sandbox credentials from Nishka IT.',
            'Verified on staging; ready for QA handoff.',
            'Retro action: add contract tests for pricing API.',
        ];
        foreach (array_slice($allTasks, 0, 15) as $task) {
            for ($c = 0; $c < rand(1, 3); $c++) {
                ProjectComment::create([
                    'project_task_id' => $task->id,
                    'user_id' => $this->u($users, $c + $task->id % count($users))->id,
                    'content' => $commentTexts[array_rand($commentTexts)],
                ]);
            }
        }

        foreach ($allTasks as $task) {
            $logCount = $task->is_completed ? rand(3, 7) : rand(1, 4);
            for ($l = 0; $l < $logCount; $l++) {
                ProjectTimeLog::create([
                    'project_task_id' => $task->id,
                    'user_id' => $task->assignee_id ?? $this->u($users, $l)->id,
                    'hours' => round(rand(1, 8) + rand(0, 3) * 0.25, 2),
                    'notes' => ['Development work', 'Code review', 'Bug fixing', 'Testing', 'Documentation', 'Meeting'][rand(0, 5)],
                    'logged_at' => $projectStarted->copy()->addDays(rand(0, (int) $projectStarted->diffInDays($ref))),
                    'is_billable' => rand(0, 4) > 0,
                ]);
            }
        }

        foreach (array_slice($allTasks, 0, 10) as $task) {
            $cl = ProjectTaskChecklist::create(['project_task_id' => $task->id, 'name' => 'Acceptance criteria', 'position' => 1]);
            foreach (['Unit tests', 'Code review', 'Docs', 'Mobile check'] as $j => $item) {
                ProjectTaskChecklistItem::create([
                    'project_task_checklist_id' => $cl->id,
                    'content' => $item,
                    'is_checked' => $task->is_completed || rand(0, 1),
                    'position' => ($j + 1) * 1000,
                ]);
            }
        }

        foreach (array_slice($allTasks, 0, 18) as $task) {
            ProjectTaskCustomFieldValue::firstOrCreate(
                ['project_task_id' => $task->id, 'custom_field_id' => $envField->id],
                ['value' => ['Development', 'Staging', 'Production'][rand(0, 2)]]
            );
            if (rand(0, 1)) {
                ProjectTaskCustomFieldValue::firstOrCreate(
                    ['project_task_id' => $task->id, 'custom_field_id' => $reviewed->id],
                    ['value' => $task->is_completed ? '1' : '0']
                );
            }
        }

        ProjectScopeChange::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Add PunchOut phase-1 scope'],
            ['requested_by' => $this->u($users, 2)->id, 'description' => 'Customer procurement asked for OCI round-trip on top of fixed catalog.', 'type' => 'addition', 'cost_impact' => 42000, 'days_impact' => 6, 'status' => 'approved', 'approved_by' => $users[0]->id, 'approved_at' => $ref->copy()->subWeeks(5)]
        );
        ProjectScopeChange::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Multi-currency display'],
            ['requested_by' => $this->u($users, 3)->id, 'description' => 'Optional USD/EUR list prices alongside INR — pending legal.', 'type' => 'addition', 'cost_impact' => 28000, 'days_impact' => 5, 'status' => 'pending']
        );

        $pm = $this->u($users, max(1, count($users) - 1));
        foreach ([10, 8, 6, 4, 2, 0] as $wi) {
            $ws = $ref->copy()->subWeeks($wi)->startOfWeek();
            ProjectWeeklyUpdate::firstOrCreate(
                ['project_id' => $project->id, 'week_start' => $ws->toDateString()],
                [
                    'created_by' => $pm->id,
                    'title' => 'Weekly update — '.$ws->format('M j'),
                    'period_type' => 'weekly',
                    'week_end' => $ws->copy()->endOfWeek()->toDateString(),
                    'summary' => 'Progress on catalogue, checkout hardening, and integration testing. Stakeholder demo '.($wi <= 6 ? 'completed' : 'scheduled').'.',
                    'next_steps' => 'Continue payment adapters; prep UAT checklist.',
                    'blockers' => $wi === 8 ? 'Waiting on VPN whitelisting for client QA.' : null,
                    'qa_approved_by' => $this->u($users, 5)->id,
                    'qa_approved_at' => $ref->copy()->subWeeks(max(0, $wi - 1)),
                ]
            );
        }

        $chat = [
            'Stand-up: focus on checkout regression today.',
            'Nishka shared updated price file — imported to staging.',
            'Please review architecture note on PunchOut before EOD.',
        ];
        foreach ($chat as $j => $msg) {
            ProjectMessage::firstOrCreate(
                ['project_id' => $project->id, 'body' => $msg],
                ['user_id' => $this->u($users, $j)->id]
            );
        }

        $folder = ProjectFolder::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Design & UX'],
            ['created_by' => $this->u($users, 2)->id, 'position' => 1]
        );
        ProjectFolder::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Wireframes', 'parent_id' => $folder->id],
            ['created_by' => $this->u($users, 2)->id, 'position' => 1]
        );
        ProjectFolder::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'API specs'],
            ['created_by' => $this->u($users, 1)->id, 'position' => 2]
        );

        ProjectSavedView::firstOrCreate(
            ['project_id' => $project->id, 'user_id' => $users[0]->id, 'name' => 'Critical path'],
            ['filters' => ['priority' => 'critical'], 'sort_by' => 'due_date', 'sort_direction' => 'asc', 'view_type' => 'list', 'is_shared' => true]
        );

        RecurringTaskPattern::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Weekly program sync with Nishka'],
            ['task_list_id' => $sections[0]->id, 'frequency' => 'weekly', 'day_of_week' => 3, 'next_run_date' => $ref->copy()->next('Wednesday'), 'is_active' => true, 'created_by' => $users[0]->id, 'priority' => 'medium']
        );

        foreach ($users as $i => $user) {
            UserCapacity::firstOrCreate(
                ['project_id' => $project->id, 'user_id' => $user->id],
                ['weekly_capacity_hours' => $i === 0 ? 12 : ($i % 5 === 0 ? 24 : 36)]
            );
        }

        foreach (array_slice($allTasks, 0, 12) as $task) {
            ProjectActivity::create(['project_task_id' => $task->id, 'user_id' => $this->u($users, (int) $task->id)->id, 'type' => 'created', 'new_value' => $task->title]);
        }
    }

    /**
     * @param  list<User>  $users
     */
    private function seedAppDeftBillingProject(
        Organization $org,
        array $users,
        Client $client,
        Carbon $ref,
        Carbon $projectStarted,
    ): void {
        $project = Project::firstOrCreate(
            ['organization_id' => $org->id, 'name' => self::BILLING_PROJECT_NAME],
            [
                'owner_id' => $users[0]->id,
                'client_id' => $client->id,
                'description' => 'Embedded squad for Meridian Analytics: streaming metrics, usage-based billing views, and self-serve reporting. Time & materials; active retainer since '.$projectStarted->format('M Y').'.',
                'status' => 'in_progress',
                'priority' => 'critical',
                'color' => '#F97316',
                'start_date' => $projectStarted->toDateString(),
                'end_date' => $ref->copy()->addMonths(6)->toDateString(),
                'visibility' => 'organization',
                'project_type' => 'billing',
                'budget' => 920000,
                'hourly_rate' => 3100,
                'srs_url' => 'https://docs.google.com/document/d/meridian-analytics-srs',
                'design_url' => 'https://figma.com/file/meridian-dashboard',
                'design_status' => 'approved',
                'design_approved_by' => $this->u($users, 2)->id,
                'design_approved_at' => $ref->copy()->subWeeks(11),
            ]
        );

        foreach ($users as $i => $user) {
            if (! $project->members()->where('user_id', $user->id)->exists()) {
                $project->members()->attach($user->id, ['role' => $i === 0 ? 'manager' : 'member']);
            }
        }

        $bs1 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Meridian — Iteration 1 (ingest)'],
            ['start_date' => $projectStarted->toDateString(), 'end_date' => $projectStarted->copy()->addWeeks(3)->toDateString(), 'status' => 'completed']
        );
        $bs2 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Meridian — Iteration 2 (dashboards)'],
            ['start_date' => $projectStarted->copy()->addWeeks(3)->toDateString(), 'end_date' => $projectStarted->copy()->addWeeks(7)->toDateString(), 'status' => 'completed']
        );
        $bs3 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Meridian — Iteration 3 (billing views)'],
            ['start_date' => $projectStarted->copy()->addWeeks(7)->toDateString(), 'end_date' => $ref->copy()->subWeeks(1)->toDateString(), 'status' => 'completed']
        );
        $bs4 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Meridian — Iteration 4 (reporting GA)'],
            ['start_date' => $ref->copy()->subWeeks(1)->toDateString(), 'end_date' => $ref->copy()->addWeeks(3)->toDateString(), 'status' => 'active']
        );

        $statuses = $project->statuses;
        $openStatus = $statuses->firstWhere('slug', 'open');
        $inProgressStatus = $statuses->firstWhere('slug', 'in_progress');
        $completedStatus = $statuses->firstWhere('slug', 'completed');

        $labels = [];
        foreach ([['Analytics', '#3B82F6'], ['Charts', '#10B981'], ['API', '#8B5CF6'], ['Critical Path', '#EF4444'], ['Revenue', '#F59E0B']] as [$n, $c]) {
            $labels[] = ProjectLabel::firstOrCreate(['project_id' => $project->id, 'name' => $n], ['color' => $c]);
        }

        $m1 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Ingest & KPI foundation'], ['due_date' => $projectStarted->copy()->addMonths(1)->toDateString(), 'status' => 'completed']);
        $m2 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Customer-facing dashboards'], ['due_date' => $ref->copy()->subWeeks(4)->toDateString(), 'status' => 'completed']);
        $m3 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Report builder GA'], ['due_date' => $ref->copy()->addMonths(1)->toDateString(), 'status' => 'open']);

        $sections = [];
        foreach (['Dashboard Widgets', 'User Analytics', 'Revenue Tracking', 'Report Builder', 'Infrastructure'] as $i => $name) {
            $sections[] = TaskList::firstOrCreate(['project_id' => $project->id, 'name' => $name], ['position' => ($i + 1) * 1000]);
        }

        if ($project->tasks()->count() > 5) {
            $this->command?->info('  AppDeft billing project already has tasks, skipping task creation.');

            return;
        }

        $taskDefs = [
            [$sections[0]->id, 'KPI cards (MRR, ARR, Churn, LTV)', 'task', 'critical', $completedStatus, $m1, 1, 12, true],
            [$sections[0]->id, 'Real-time active users widget', 'task', 'high', $completedStatus, $m1, 1, 8, true],
            [$sections[0]->id, 'Revenue trend line chart', 'task', 'high', $inProgressStatus, $m1, 1, 10, false],
            [$sections[0]->id, 'Subscription funnel visualization', 'story', 'medium', $openStatus, $m1, 2, 8, false],
            [$sections[0]->id, 'Widget drag-and-drop customization', 'epic', 'medium', $openStatus, $m2, 1, 16, false],
            [$sections[1]->id, 'User segmentation engine', 'task', 'high', $inProgressStatus, $m1, 2, 14, false],
            [$sections[1]->id, 'Cohort analysis charts', 'task', 'medium', $openStatus, $m2, 1, 12, false],
            [$sections[1]->id, 'User journey mapping', 'story', 'medium', $openStatus, $m2, 3, 10, false],
            [$sections[1]->id, 'Session replay integration', 'story', 'low', $openStatus, $m3, 2, 16, false],
            [$sections[2]->id, 'Stripe webhook processor', 'task', 'critical', $completedStatus, $m1, 2, 10, true],
            [$sections[2]->id, 'MRR calculation engine', 'task', 'critical', $inProgressStatus, $m1, 2, 12, false],
            [$sections[2]->id, 'Revenue breakdown by plan/country', 'task', 'high', $openStatus, $m2, 1, 8, false],
            [$sections[2]->id, 'Invoice & payment history', 'task', 'medium', $openStatus, $m2, 2, 10, false],
            [$sections[2]->id, 'Churn prediction model', 'epic', 'high', $openStatus, $m3, 2, 20, false],
            [$sections[3]->id, 'Report template system', 'task', 'high', $openStatus, $m3, 1, 16, false],
            [$sections[3]->id, 'Drag-and-drop report builder UI', 'epic', 'high', $openStatus, $m3, 1, 20, false],
            [$sections[3]->id, 'PDF/CSV export engine', 'task', 'medium', $openStatus, $m3, 2, 8, false],
            [$sections[3]->id, 'Scheduled report delivery (email)', 'task', 'medium', $openStatus, $m3, 4, 6, false],
            [$sections[4]->id, 'ClickHouse analytics database setup', 'task', 'critical', $completedStatus, $m1, 4, 8, true],
            [$sections[4]->id, 'Real-time event pipeline (Kafka)', 'task', 'high', $inProgressStatus, $m1, 4, 14, false],
            [$sections[4]->id, 'CDN & asset optimization', 'task', 'medium', $openStatus, $m2, 4, 4, false],
            [$sections[4]->id, 'Data retention & archival policy', 'task', 'low', $openStatus, $m3, 4, 6, false],
        ];

        $allTasks = [];
        $labelIds = collect($labels)->pluck('id')->toArray();
        foreach ($taskDefs as $i => $td) {
            [$listId, $title, $issueType, $priority, $status, $milestone, $assigneeIx, $hours, $isDone] = $td;
            $assignee = $this->u($users, $assigneeIx);
            $task = ProjectTask::create([
                'project_id' => $project->id,
                'task_list_id' => $listId,
                'title' => $title,
                'description' => 'Meridian backlog item: '.$title,
                'issue_type' => $issueType,
                'priority' => $priority,
                'project_status_id' => $status?->id,
                'status' => $status?->slug ?? 'open',
                'milestone_id' => $milestone?->id,
                'assignee_id' => $assignee->id,
                'estimated_hours' => $hours,
                'is_completed' => $isDone,
                'completed_at' => $isDone ? $ref->copy()->subDays(rand(10, 80)) : null,
                'start_date' => $isDone ? $projectStarted->copy()->addDays(rand(5, 40))->toDateString() : (($status?->slug ?? '') === 'in_progress' ? $ref->copy()->subWeeks(rand(1, 4))->toDateString() : null),
                'due_date' => $ref->copy()->addWeeks(rand(1, 8))->toDateString(),
                'position' => ($i + 1) * 1000,
                'story_points' => rand(2, 13),
            ]);
            $allTasks[] = $task;
            if (count($labelIds) > 0) {
                $pick = $labelIds;
                shuffle($pick);
                $task->labels()->sync(array_slice($pick, 0, min(2, count($pick))));
            }
        }

        $bs1->tasks()->syncWithoutDetaching(collect($allTasks)->slice(0, 6)->pluck('id'));
        $bs2->tasks()->syncWithoutDetaching(collect($allTasks)->slice(6, 6)->pluck('id'));
        $bs3->tasks()->syncWithoutDetaching(collect($allTasks)->slice(12, 6)->pluck('id'));
        $bs4->tasks()->syncWithoutDetaching(collect($allTasks)->slice(18, 4)->pluck('id'));

        foreach ($allTasks as $task) {
            $logCount = $task->is_completed ? rand(5, 10) : rand(2, 6);
            for ($l = 0; $l < $logCount; $l++) {
                ProjectTimeLog::create([
                    'project_task_id' => $task->id,
                    'user_id' => $task->assignee_id ?? $this->u($users, $l)->id,
                    'hours' => round(rand(1, 8) + rand(0, 3) * 0.25, 2),
                    'notes' => ['Development', 'Client workshop', 'Architecture', 'Testing', 'Sprint review', 'Billable research'][rand(0, 5)],
                    'logged_at' => $projectStarted->copy()->addDays(rand(0, (int) max(1, $projectStarted->diffInDays($ref)))),
                    'is_billable' => true,
                ]);
            }
        }

        for ($w = 12; $w >= 1; $w--) {
            $weekStart = $ref->copy()->subWeeks($w)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $bw = ProjectBillingWeek::firstOrCreate(
                ['project_id' => $project->id, 'week_start' => $weekStart->toDateString()],
                [
                    'week_end' => $weekEnd->toDateString(),
                    'total_actual_hours' => rand(140, 220),
                    'total_billable_hours' => rand(120, 200),
                    'total_amount' => rand(280000, 520000),
                    'locked_by' => $w > 2 ? $users[0]->id : null,
                    'locked_at' => $w > 2 ? $ref->copy()->subWeeks($w - 1) : null,
                ]
            );

            $slice = array_slice($users, 1, min(8, count($users) - 1));
            foreach ($slice as $user) {
                $actual = round(rand(6, 28) + rand(0, 3) * 0.25, 2);
                ProjectBillingEntry::firstOrCreate(
                    ['billing_week_id' => $bw->id, 'user_id' => $user->id],
                    ['actual_hours' => $actual, 'billable_hours' => round($actual * 0.92, 2)]
                );
            }
        }

        foreach (array_slice($allTasks, 0, 10) as $task) {
            ProjectComment::create([
                'project_task_id' => $task->id,
                'user_id' => $this->u($users, (int) $task->id)->id,
                'content' => 'Client-visible note: on track for next milestone; hours logged against T&M.',
            ]);
            $cl = ProjectTaskChecklist::create(['project_task_id' => $task->id, 'name' => 'Definition of done', 'position' => 1]);
            foreach (['Shipped', 'Tested', 'Reviewed', 'Demoed to Meridian'] as $j => $item) {
                ProjectTaskChecklistItem::create([
                    'project_task_checklist_id' => $cl->id,
                    'content' => $item,
                    'is_checked' => $task->is_completed || ($j < 2 && rand(0, 1)),
                    'position' => ($j + 1) * 1000,
                ]);
            }
        }

        ProjectScopeChange::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Expand usage-based billing explorer'],
            ['requested_by' => $this->u($users, 3)->id, 'description' => 'Meridian PM asked for drill-down by customer segment.', 'type' => 'addition', 'cost_impact' => 95000, 'days_impact' => 12, 'status' => 'approved', 'approved_by' => $users[0]->id, 'approved_at' => $ref->copy()->subWeeks(4)]
        );

        foreach ([8, 6, 4, 2, 0] as $wi) {
            $ws = $ref->copy()->subWeeks($wi)->startOfWeek();
            ProjectWeeklyUpdate::firstOrCreate(
                ['project_id' => $project->id, 'week_start' => $ws->toDateString()],
                [
                    'created_by' => $this->u($users, 1)->id,
                    'title' => 'Meridian weekly — '.$ws->format('M j'),
                    'period_type' => 'weekly',
                    'week_end' => $ws->copy()->endOfWeek()->toDateString(),
                    'summary' => 'Burn rate within retainer. Focus on MRR accuracy and pipeline latency.',
                    'next_steps' => 'Ship report-builder MVP; schedule billing UAT.',
                    'blockers' => null,
                ]
            );
        }

        foreach ($users as $user) {
            UserCapacity::firstOrCreate(['project_id' => $project->id, 'user_id' => $user->id], ['weekly_capacity_hours' => 32]);
        }

        foreach (['Hours look good for last cycle — please lock week.', 'Meridian asked for export cap raise; handled in scope change.', 'Demo deck updated in shared folder.'] as $j => $msg) {
            ProjectMessage::firstOrCreate(
                ['project_id' => $project->id, 'body' => $msg],
                ['user_id' => $this->u($users, $j)->id]
            );
        }

        ProjectFolder::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Client deliverables'],
            ['created_by' => $users[0]->id, 'position' => 1]
        );
    }
}
