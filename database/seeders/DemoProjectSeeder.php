<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\EmployeeAsset;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEducation;
use App\Models\EmployeeExperience;
use App\Models\EmployeeProfile;
use App\Models\EmployeeSkill;
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
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoProjectSeeder extends Seeder
{
    private Organization $org;
    private array $users = [];
    private array $clients = [];

    public function run(): void
    {
        $this->org = Organization::firstOrFail();
        $owner = User::find($this->org->owner_id);

        // ═══════════════════════════════════════════════════════════════
        // TEAM MEMBERS (8 users total including owner)
        // ═══════════════════════════════════════════════════════════════
        $this->users[] = $owner;

        $teamData = [
            ['name' => 'Priya Sharma',    'email' => 'priya@demo.test',    'department' => 'Engineering',  'designation' => 'Senior Frontend Developer', 'employment_type' => 'full_time'],
            ['name' => 'Rahul Verma',     'email' => 'rahul@demo.test',    'department' => 'Engineering',  'designation' => 'Backend Developer',         'employment_type' => 'full_time'],
            ['name' => 'Ananya Patel',    'email' => 'ananya@demo.test',   'department' => 'Design',       'designation' => 'UI/UX Designer',            'employment_type' => 'full_time'],
            ['name' => 'Vikram Singh',    'email' => 'vikram@demo.test',   'department' => 'Engineering',  'designation' => 'DevOps Engineer',            'employment_type' => 'full_time'],
            ['name' => 'Neha Gupta',      'email' => 'neha@demo.test',     'department' => 'QA',           'designation' => 'QA Lead',                   'employment_type' => 'full_time'],
            ['name' => 'Arjun Reddy',     'email' => 'arjun@demo.test',    'department' => 'Engineering',  'designation' => 'Junior Developer',          'employment_type' => 'intern'],
            ['name' => 'Meera Joshi',     'email' => 'meera@demo.test',    'department' => 'Project Mgmt', 'designation' => 'Project Manager',           'employment_type' => 'full_time'],
        ];

        foreach ($teamData as $i => $td) {
            $user = User::firstOrCreate(
                ['email' => $td['email']],
                ['name' => $td['name'], 'password' => Hash::make('password')]
            );
            $this->users[] = $user;

            if (!$this->org->members()->where('user_id', $user->id)->exists()) {
                $this->org->members()->attach($user->id, ['role' => 'member']);
            }

            // Employee profiles
            EmployeeProfile::firstOrCreate(
                ['organization_id' => $this->org->id, 'user_id' => $user->id],
                [
                    'employee_id'            => 'EMP-' . str_pad($i + 2, 3, '0', STR_PAD_LEFT),
                    'designation'            => $td['designation'],
                    'department'             => $td['department'],
                    'date_of_joining'        => now()->subMonths(rand(3, 36)),
                    'employment_type'        => $td['employment_type'],
                    'reporting_manager_id'   => $owner->id,
                    'work_location'          => ['Jaipur Office', 'Remote', 'Bangalore Office'][rand(0, 2)],
                    'shift'                  => 'General (9AM-6PM)',
                    'phone'                  => '+91 ' . rand(70000, 99999) . rand(10000, 99999),
                    'date_of_birth'          => now()->subYears(rand(22, 38))->subDays(rand(0, 365)),
                    'gender'                 => $i % 2 === 0 ? 'female' : 'male',
                    'blood_group'            => ['A+', 'B+', 'O+', 'AB+', 'A-', 'O-'][rand(0, 5)],
                    'nationality'            => 'Indian',
                    'emergency_contact_name' => 'Family Contact',
                    'emergency_contact_phone'=> '+91 ' . rand(70000, 99999) . rand(10000, 99999),
                    'current_address'        => rand(1, 500) . ', Sector ' . rand(1, 50) . ', Jaipur, Rajasthan',
                    'bank_name'              => ['HDFC Bank', 'ICICI Bank', 'SBI', 'Axis Bank'][rand(0, 3)],
                    'ifsc_code'              => 'HDFC0001234',
                    'status'                 => 'active',
                ]
            );

            $profile = EmployeeProfile::where('user_id', $user->id)->where('organization_id', $this->org->id)->first();

            if ($profile && $profile->education()->count() === 0) {
                EmployeeEducation::create([
                    'employee_profile_id' => $profile->id,
                    'degree'        => ['B.Tech', 'M.Tech', 'BCA', 'MCA', 'B.Sc CS'][rand(0, 4)],
                    'institution'   => ['IIT Delhi', 'NIT Jaipur', 'BITS Pilani', 'Delhi University', 'Manipal University'][rand(0, 4)],
                    'field_of_study'=> 'Computer Science',
                    'start_year'    => 2014 + rand(0, 4),
                    'end_year'      => 2018 + rand(0, 4),
                    'grade'         => ['8.5 CGPA', '9.0 CGPA', '7.8 CGPA', 'First Class'][rand(0, 3)],
                ]);

                EmployeeExperience::create([
                    'employee_profile_id' => $profile->id,
                    'company'      => ['TCS', 'Infosys', 'Wipro', 'HCL', 'Tech Mahindra', 'Freelance'][rand(0, 5)],
                    'designation'  => ['Software Engineer', 'Junior Developer', 'Intern', 'Associate'][rand(0, 3)],
                    'start_date'   => now()->subYears(rand(2, 5)),
                    'end_date'     => now()->subMonths(rand(1, 12)),
                    'description'  => 'Worked on web applications using modern frameworks.',
                ]);

                EmployeeSkill::create(['employee_profile_id' => $profile->id, 'name' => ['Laravel', 'React', 'Vue.js', 'Node.js', 'Python', 'Docker', 'AWS'][rand(0, 6)], 'category' => 'skill']);
                EmployeeSkill::create(['employee_profile_id' => $profile->id, 'name' => ['AWS Certified', 'Scrum Master', 'PMP', 'Google Cloud'][rand(0, 3)], 'category' => 'certification', 'issued_by' => 'Certification Authority', 'issued_date' => now()->subMonths(rand(1, 24))]);

                EmployeeAsset::create([
                    'employee_profile_id' => $profile->id,
                    'type'          => 'laptop',
                    'name'          => ['MacBook Pro 14"', 'Dell XPS 15', 'ThinkPad X1 Carbon', 'MacBook Air M2'][rand(0, 3)],
                    'asset_tag'     => 'ASSET-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
                    'serial_number' => strtoupper(Str::random(12)),
                    'assigned_date' => now()->subMonths(rand(1, 12)),
                ]);
            }
        }

        // ═══════════════════════════════════════════════════════════════
        // CLIENTS
        // ═══════════════════════════════════════════════════════════════
        $this->clients[] = Client::firstOrCreate(
            ['organization_id' => $this->org->id, 'email' => 'contact@acmecorp.com'],
            ['name' => 'Rajesh Kumar', 'company' => 'Acme Corporation', 'phone' => '+91 98765 43210', 'timezone' => 'Asia/Kolkata', 'notes' => 'Enterprise client, long-term contract']
        );
        $this->clients[] = Client::firstOrCreate(
            ['organization_id' => $this->org->id, 'email' => 'hello@startupx.io'],
            ['name' => 'Sarah Wilson', 'company' => 'StartupX', 'phone' => '+1 555-0199', 'timezone' => 'America/New_York', 'notes' => 'Startup, fast-paced, weekly sprints']
        );

        // ═══════════════════════════════════════════════════════════════
        // PROJECT 1: FIXED — E-Commerce Platform Redesign
        // ═══════════════════════════════════════════════════════════════
        $this->seedFixedProject();

        // ═══════════════════════════════════════════════════════════════
        // PROJECT 2: BILLING — SaaS Dashboard Development
        // ═══════════════════════════════════════════════════════════════
        $this->seedBillingProject();

        $this->command->info('Demo projects seeded successfully!');
    }

    private function seedFixedProject(): void
    {
        $project = Project::firstOrCreate(
            ['organization_id' => $this->org->id, 'name' => 'E-Commerce Platform Redesign'],
            [
                'owner_id'      => $this->users[0]->id,
                'client_id'     => $this->clients[0]->id,
                'description'   => 'Complete redesign of the Acme Corp e-commerce platform. Includes new UI/UX, performance optimization, mobile responsiveness, payment gateway integration, and admin dashboard.',
                'status'        => 'in_progress',
                'priority'      => 'high',
                'color'         => '#6366F1',
                'start_date'    => now()->subWeeks(8),
                'end_date'      => now()->addWeeks(8),
                'visibility'    => 'organization',
                'project_type'  => 'fixed',
                'budget'        => 450000,
                'hourly_rate'   => 2500,
                'srs_url'       => 'https://docs.google.com/document/d/srs-ecommerce',
                'design_url'    => 'https://figma.com/file/ecommerce-redesign',
                'design_status' => 'approved',
                'design_approved_by' => $this->users[0]->id,
                'design_approved_at' => now()->subWeeks(6),
                'design_feedback'    => 'Design approved with minor color adjustments for accessibility.',
            ]
        );

        // Add all team members
        foreach ($this->users as $i => $user) {
            if (!$project->members()->where('user_id', $user->id)->exists()) {
                $project->members()->attach($user->id, ['role' => $i === 0 ? 'manager' : ($i === 6 ? 'manager' : 'member')]);
            }
        }

        // Custom statuses (added on top of defaults)
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

        // Labels
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

        // Custom fields
        $envField = ProjectCustomField::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Environment'],
            ['type' => 'dropdown', 'options' => ['Development', 'Staging', 'Production'], 'position' => 1]
        );
        $prUrl = ProjectCustomField::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'PR Link'],
            ['type' => 'url', 'position' => 2]
        );
        $reviewed = ProjectCustomField::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Code Reviewed'],
            ['type' => 'checkbox', 'position' => 3]
        );
        $complexity = ProjectCustomField::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Complexity Score'],
            ['type' => 'number', 'position' => 4]
        );

        // Milestones
        $m1 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Design Phase'], ['description' => 'Complete all UI/UX designs and prototypes', 'due_date' => now()->subWeeks(4), 'status' => 'completed']);
        $m2 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Core Development'], ['description' => 'Build core features: product catalog, cart, checkout', 'due_date' => now()->addWeeks(2), 'status' => 'open']);
        $m3 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Payment Integration'], ['description' => 'Razorpay, Stripe, UPI integration', 'due_date' => now()->addWeeks(4), 'status' => 'open']);
        $m4 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Launch & Go-Live'], ['description' => 'Final QA, deployment, launch', 'due_date' => now()->addWeeks(8), 'status' => 'open']);

        // Task Lists (sections)
        $sections = [];
        foreach (['Product Catalog', 'Shopping Cart & Checkout', 'User Authentication', 'Admin Dashboard', 'API & Integrations', 'DevOps & Deployment'] as $i => $name) {
            $sections[] = TaskList::firstOrCreate(
                ['project_id' => $project->id, 'name' => $name],
                ['position' => ($i + 1) * 1000]
            );
        }

        // Sprints
        $sprint1 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 1 - Foundation'],
            ['start_date' => now()->subWeeks(6), 'end_date' => now()->subWeeks(4), 'status' => 'completed']
        );
        $sprint2 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 2 - Core Features'],
            ['start_date' => now()->subWeeks(4), 'end_date' => now()->subWeeks(2), 'status' => 'completed']
        );
        $sprint3 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 3 - Integrations'],
            ['start_date' => now()->subWeeks(2), 'end_date' => now(), 'status' => 'active']
        );
        $sprint4 = Sprint::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Sprint 4 - Polish & Launch'],
            ['start_date' => now(), 'end_date' => now()->addWeeks(2), 'status' => 'planning']
        );

        $skipTasks = $project->tasks()->count() > 5;
        if ($skipTasks) {
            $this->command->info('  Fixed project already has tasks, skipping task creation.');
        }

        // ── TASKS ─────────────────────────────────────────────────────
        $allTasks = [];
        if ($skipTasks) {
            $allTasks = $project->tasks()->whereNull('parent_task_id')->get()->all();
            goto after_tasks;
        }
        $taskDefs = [
            // Section 0: Product Catalog
            [$sections[0]->id, 'Design product listing page',              'task',  'critical', $completedStatus, $m1, $this->users[3], 8,  true,  now()->subWeeks(7), now()->subWeeks(5)],
            [$sections[0]->id, 'Implement product grid with filters',       'task',  'high',     $completedStatus, $m2, $this->users[1], 16, true,  now()->subWeeks(6), now()->subWeeks(3)],
            [$sections[0]->id, 'Build product detail page',                 'task',  'high',     $inProgressStatus,$m2, $this->users[1], 12, false, now()->subWeeks(4), now()->addDays(2)],
            [$sections[0]->id, 'Add product search with Algolia',           'task',  'medium',   $inProgressStatus,$m2, $this->users[2], 10, false, now()->subWeeks(3), now()->addDays(5)],
            [$sections[0]->id, 'Product image zoom and gallery',            'story', 'medium',   $openStatus,      $m2, $this->users[1], 6,  false, now()->subWeeks(2), now()->addWeeks(1)],
            [$sections[0]->id, 'Product reviews & ratings system',          'story', 'low',      $openStatus,      $m3, $this->users[2], 14, false, null, now()->addWeeks(3)],
            [$sections[0]->id, 'Fix product price formatting bug',          'bug',   'high',     $completedStatus, $m2, $this->users[1], 2,  true,  now()->subWeeks(2), now()->subWeeks(1)],
            [$sections[0]->id, 'Product comparison feature',                'epic',  'low',      $deferredStatus,  null,$this->users[2], 20, false, null, now()->addWeeks(6)],

            // Section 1: Shopping Cart & Checkout
            [$sections[1]->id, 'Build shopping cart component',             'task',  'critical', $completedStatus, $m2, $this->users[1], 12, true,  now()->subWeeks(5), now()->subWeeks(3)],
            [$sections[1]->id, 'Implement cart persistence (localStorage)', 'task',  'high',     $completedStatus, $m2, $this->users[1], 4,  true,  now()->subWeeks(4), now()->subWeeks(3)],
            [$sections[1]->id, 'Multi-step checkout flow',                  'task',  'critical', $inProgressStatus,$m2, $this->users[1], 16, false, now()->subWeeks(3), now()->addDays(3)],
            [$sections[1]->id, 'Address form with Google Places API',       'task',  'medium',   $inReview,        $m2, $this->users[2], 8,  false, now()->subWeeks(2), now()->addDays(1)],
            [$sections[1]->id, 'Order confirmation email template',         'task',  'medium',   $openStatus,      $m3, $this->users[3], 4,  false, null, now()->addWeeks(2)],
            [$sections[1]->id, 'Cart quantity update not syncing',          'bug',   'critical', $qaStatus,        $m2, $this->users[5], 3,  false, now()->subDays(3), now()->addDays(1)],
            [$sections[1]->id, 'Apply coupon/promo code system',            'story', 'medium',   $openStatus,      $m3, $this->users[2], 10, false, null, now()->addWeeks(3)],

            // Section 2: User Authentication
            [$sections[2]->id, 'Login & Registration pages',                'task',  'high',     $completedStatus, $m1, $this->users[2], 8,  true,  now()->subWeeks(7), now()->subWeeks(6)],
            [$sections[2]->id, 'OAuth (Google, Facebook) integration',      'task',  'medium',   $completedStatus, $m2, $this->users[2], 6,  true,  now()->subWeeks(5), now()->subWeeks(4)],
            [$sections[2]->id, 'Password reset flow',                       'task',  'high',     $completedStatus, $m2, $this->users[2], 4,  true,  now()->subWeeks(5), now()->subWeeks(4)],
            [$sections[2]->id, 'User profile & order history page',         'task',  'medium',   $inProgressStatus,$m2, $this->users[1], 10, false, now()->subWeeks(2), now()->addDays(4)],
            [$sections[2]->id, 'Two-factor authentication',                 'story', 'low',      $openStatus,      $m3, $this->users[2], 8,  false, null, now()->addWeeks(4)],

            // Section 3: Admin Dashboard
            [$sections[3]->id, 'Admin dashboard wireframes',                'task',  'high',     $completedStatus, $m1, $this->users[3], 6,  true,  now()->subWeeks(7), now()->subWeeks(6)],
            [$sections[3]->id, 'Order management system',                   'task',  'high',     $inProgressStatus,$m2, $this->users[2], 16, false, now()->subWeeks(3), now()->addWeeks(1)],
            [$sections[3]->id, 'Product inventory management',              'task',  'medium',   $openStatus,      $m3, $this->users[2], 12, false, null, now()->addWeeks(3)],
            [$sections[3]->id, 'Sales analytics dashboard',                 'story', 'medium',   $openStatus,      $m4, $this->users[1], 14, false, null, now()->addWeeks(5)],
            [$sections[3]->id, 'Customer management panel',                 'task',  'low',      $openStatus,      $m4, $this->users[2], 8,  false, null, now()->addWeeks(6)],

            // Section 4: API & Integrations
            [$sections[4]->id, 'RESTful API architecture design',           'task',  'critical', $completedStatus, $m1, $this->users[2], 8,  true,  now()->subWeeks(8), now()->subWeeks(7)],
            [$sections[4]->id, 'Razorpay payment gateway integration',      'task',  'critical', $inProgressStatus,$m3, $this->users[2], 12, false, now()->subWeeks(1), now()->addWeeks(2)],
            [$sections[4]->id, 'Stripe international payments',             'task',  'high',     $openStatus,      $m3, $this->users[2], 10, false, null, now()->addWeeks(3)],
            [$sections[4]->id, 'Shiprocket shipping API integration',       'task',  'medium',   $openStatus,      $m3, $this->users[4], 8,  false, null, now()->addWeeks(3)],
            [$sections[4]->id, 'SMS notification (MSG91) integration',      'task',  'low',      $openStatus,      $m4, $this->users[4], 6,  false, null, now()->addWeeks(5)],
            [$sections[4]->id, 'API rate limiting not working correctly',   'bug',   'high',     $inProgressStatus,$m2, $this->users[4], 4,  false, now()->subDays(5), now()->addDays(2)],

            // Section 5: DevOps & Deployment
            [$sections[5]->id, 'Set up CI/CD pipeline (GitHub Actions)',    'task',  'high',     $completedStatus, $m1, $this->users[4], 6,  true,  now()->subWeeks(7), now()->subWeeks(6)],
            [$sections[5]->id, 'Configure staging environment on AWS',      'task',  'high',     $completedStatus, $m2, $this->users[4], 8,  true,  now()->subWeeks(5), now()->subWeeks(4)],
            [$sections[5]->id, 'Set up Redis caching layer',               'task',  'medium',   $inProgressStatus,$m2, $this->users[4], 6,  false, now()->subWeeks(1), now()->addDays(3)],
            [$sections[5]->id, 'Production deployment checklist',           'task',  'medium',   $openStatus,      $m4, $this->users[4], 4,  false, null, now()->addWeeks(6)],
            [$sections[5]->id, 'Load testing & performance benchmark',      'task',  'high',     $openStatus,      $m4, $this->users[5], 8,  false, null, now()->addWeeks(5)],
            [$sections[5]->id, 'SSL certificate & domain configuration',    'task',  'high',     $openStatus,      $m4, $this->users[4], 2,  false, null, now()->addWeeks(7)],
        ];

        foreach ($taskDefs as $i => $td) {
            $task = ProjectTask::create([
                'project_id'        => $project->id,
                'task_list_id'      => $td[0],
                'title'             => $td[1],
                'description'       => 'Detailed description for: ' . $td[1],
                'issue_type'        => $td[2],
                'priority'          => $td[3],
                'project_status_id' => $td[4]?->id,
                'status'            => $td[4]?->slug ?? 'open',
                'milestone_id'      => $td[5]?->id,
                'assignee_id'       => $td[6]->id,
                'estimated_hours'   => $td[7],
                'is_completed'      => $td[8],
                'completed_at'      => $td[8] ? now()->subDays(rand(1, 30)) : null,
                'start_date'        => $td[9],
                'due_date'          => $td[10],
                'position'          => ($i + 1) * 1000,
                'story_points'      => rand(1, 13),
            ]);
            $allTasks[] = $task;

            // Attach 1-2 labels
            $task->labels()->sync(array_rand(array_flip(collect($labels)->pluck('id')->toArray()), rand(1, 2)));
        }

        // ── Subtasks (for first few tasks) ──────────────────────────
        foreach (array_slice($allTasks, 0, 5) as $parent) {
            for ($s = 0; $s < rand(2, 4); $s++) {
                ProjectTask::create([
                    'project_id'        => $project->id,
                    'task_list_id'      => $parent->task_list_id,
                    'parent_task_id'    => $parent->id,
                    'title'             => 'Subtask ' . ($s + 1) . ' of ' . Str::limit($parent->title, 30),
                    'status'            => $parent->status,
                    'project_status_id' => $parent->project_status_id,
                    'priority'          => 'none',
                    'assignee_id'       => $this->users[rand(1, 5)]->id,
                    'position'          => ($s + 1) * 1000,
                    'is_completed'      => $parent->is_completed,
                    'completed_at'      => $parent->completed_at,
                    'issue_type'        => 'task',
                ]);
            }
        }

        // ── Sprint tasks ────────────────────────────────────────────
        $sprint1->tasks()->syncWithoutDetaching(collect($allTasks)->slice(0, 8)->pluck('id'));
        $sprint2->tasks()->syncWithoutDetaching(collect($allTasks)->slice(8, 8)->pluck('id'));
        $sprint3->tasks()->syncWithoutDetaching(collect($allTasks)->slice(16, 8)->pluck('id'));

        // ── Task links (dependencies) ───────────────────────────────
        ProjectTaskLink::firstOrCreate(['task_id' => $allTasks[2]->id, 'linked_task_id' => $allTasks[0]->id], ['type' => 'blocked_by']);
        ProjectTaskLink::firstOrCreate(['task_id' => $allTasks[10]->id, 'linked_task_id' => $allTasks[8]->id], ['type' => 'blocked_by']);
        ProjectTaskLink::firstOrCreate(['task_id' => $allTasks[26]->id, 'linked_task_id' => $allTasks[25]->id], ['type' => 'relates_to']);

        // ── Comments ────────────────────────────────────────────────
        $commentTexts = [
            'I\'ve started working on this. Will push the initial commit today.',
            'The design mockups are ready for review. @neha please check the responsive layout.',
            'Found an edge case with empty cart state. Adding a fix in the next commit.',
            'This needs to be tested on Safari. The CSS grid behaves differently there.',
            'Updated the API response format as discussed in standup. Please review.',
            'Blocked on this — waiting for the payment gateway sandbox credentials.',
            'Completed the code review. A few minor suggestions in the PR comments.',
            'Performance improved by 40% after implementing lazy loading. 🎉',
        ];
        foreach (array_slice($allTasks, 0, 12) as $task) {
            for ($c = 0; $c < rand(1, 3); $c++) {
                ProjectComment::create([
                    'project_task_id' => $task->id,
                    'user_id'         => $this->users[rand(0, 6)]->id,
                    'content'         => $commentTexts[array_rand($commentTexts)],
                ]);
            }
        }

        // ── Time logs ───────────────────────────────────────────────
        foreach ($allTasks as $task) {
            $logCount = $task->is_completed ? rand(3, 6) : rand(0, 3);
            for ($l = 0; $l < $logCount; $l++) {
                ProjectTimeLog::create([
                    'project_task_id' => $task->id,
                    'user_id'         => $task->assignee_id ?? $this->users[rand(1, 5)]->id,
                    'hours'           => round(rand(1, 8) + rand(0, 3) * 0.25, 2),
                    'notes'           => ['Development work', 'Code review', 'Bug fixing', 'Testing', 'Documentation', 'Meeting'][rand(0, 5)],
                    'logged_at'       => now()->subDays(rand(0, 45)),
                    'is_billable'     => rand(0, 4) > 0, // 80% billable
                ]);
            }
        }

        // ── Checklists ──────────────────────────────────────────────
        foreach (array_slice($allTasks, 0, 8) as $task) {
            $cl = ProjectTaskChecklist::create(['project_task_id' => $task->id, 'name' => 'Acceptance Criteria', 'position' => 1]);
            foreach (['Unit tests written', 'Code reviewed', 'Documentation updated', 'Tested on mobile'] as $j => $item) {
                ProjectTaskChecklistItem::create([
                    'project_task_checklist_id' => $cl->id,
                    'content'    => $item,
                    'is_checked' => $task->is_completed || rand(0, 1),
                    'position'   => ($j + 1) * 1000,
                ]);
            }
        }

        // ── Custom field values ─────────────────────────────────────
        foreach (array_slice($allTasks, 0, 15) as $task) {
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

        after_tasks:
        // ── Scope changes ───────────────────────────────────────────
        ProjectScopeChange::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Add wishlist feature'],
            ['requested_by' => $this->clients[0]->id, 'description' => 'Client wants a wishlist/save-for-later feature on the product pages.', 'type' => 'addition', 'cost_impact' => 35000, 'days_impact' => 5, 'status' => 'approved', 'approved_by' => $this->users[0]->id, 'approved_at' => now()->subWeeks(2)]
        );
        ProjectScopeChange::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Multi-currency support'],
            ['requested_by' => $this->clients[0]->id, 'description' => 'Support USD, EUR, GBP along with INR.', 'type' => 'addition', 'cost_impact' => 50000, 'days_impact' => 8, 'status' => 'pending']
        );

        // ── Weekly updates ──────────────────────────────────────────
        ProjectWeeklyUpdate::firstOrCreate(
            ['project_id' => $project->id, 'week_start' => now()->subWeeks(2)->startOfWeek()],
            ['created_by' => $this->users[6]->id, 'title' => 'Week 6 Progress Update', 'period_type' => 'weekly', 'week_end' => now()->subWeeks(2)->endOfWeek(), 'summary' => 'Completed cart component, checkout flow 60% done. Design approved for admin dashboard. 2 critical bugs fixed.', 'next_steps' => 'Complete checkout flow, begin payment integration', 'blockers' => 'Waiting for Razorpay sandbox credentials from client', 'qa_approved_by' => $this->users[5]->id, 'qa_approved_at' => now()->subWeeks(1)]
        );
        ProjectWeeklyUpdate::firstOrCreate(
            ['project_id' => $project->id, 'week_start' => now()->subWeeks(1)->startOfWeek()],
            ['created_by' => $this->users[6]->id, 'title' => 'Week 7 Progress Update', 'period_type' => 'weekly', 'week_end' => now()->subWeeks(1)->endOfWeek(), 'summary' => 'Checkout flow completed. Payment integration started. Address autocomplete in review. 1 scope change approved.', 'next_steps' => 'Complete Razorpay integration, start admin dashboard order management', 'blockers' => 'None currently']
        );

        // ── Chat messages ───────────────────────────────────────────
        $chatMessages = [
            'Good morning team! Let\'s aim to close the sprint 3 tasks by Friday.',
            'The staging deployment is ready. Please test the checkout flow.',
            'I\'ve updated the design tokens. Frontend team, please pull the latest.',
            'Quick heads up — client meeting moved to 3 PM tomorrow.',
            'The Razorpay sandbox is now configured. @rahul you can start integration.',
            'Code review done for PR #42. Looks good, minor styling fix needed.',
            'Sprint retrospective scheduled for Monday 11 AM.',
        ];
        foreach ($chatMessages as $j => $msg) {
            ProjectMessage::firstOrCreate(
                ['project_id' => $project->id, 'body' => $msg],
                ['user_id' => $this->users[$j % count($this->users)]->id]
            );
        }

        // ── Document folders ────────────────────────────────────────
        $docFolder = ProjectFolder::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Design Assets'],
            ['created_by' => $this->users[3]->id, 'position' => 1]
        );
        ProjectFolder::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Wireframes', 'parent_id' => $docFolder->id],
            ['created_by' => $this->users[3]->id, 'position' => 1]
        );
        ProjectFolder::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'API Documentation'],
            ['created_by' => $this->users[2]->id, 'position' => 2]
        );
        ProjectFolder::firstOrCreate(
            ['project_id' => $project->id, 'name' => 'Meeting Notes'],
            ['created_by' => $this->users[6]->id, 'position' => 3]
        );

        // ── Saved views ─────────────────────────────────────────────
        ProjectSavedView::firstOrCreate(
            ['project_id' => $project->id, 'user_id' => $this->users[0]->id, 'name' => 'My High Priority'],
            ['filters' => ['priority' => 'high'], 'sort_by' => 'due_date', 'sort_direction' => 'asc', 'view_type' => 'list']
        );
        ProjectSavedView::firstOrCreate(
            ['project_id' => $project->id, 'user_id' => $this->users[0]->id, 'name' => 'Bugs Only'],
            ['filters' => ['issueType' => 'bug'], 'view_type' => 'list', 'is_shared' => true]
        );
        ProjectSavedView::firstOrCreate(
            ['project_id' => $project->id, 'user_id' => $this->users[0]->id, 'name' => 'Overdue Tasks'],
            ['filters' => ['status' => 'open'], 'sort_by' => 'due_date', 'sort_direction' => 'asc', 'group_by' => 'assignee', 'view_type' => 'list', 'is_shared' => true]
        );

        // ── Recurring tasks ─────────────────────────────────────────
        RecurringTaskPattern::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Weekly Code Review Session'],
            ['task_list_id' => $sections[0]->id, 'description' => 'Review all PRs from the week', 'frequency' => 'weekly', 'day_of_week' => 5, 'next_run_date' => now()->next('Friday'), 'is_active' => true, 'created_by' => $this->users[0]->id, 'priority' => 'medium', 'assignee_id' => $this->users[2]->id]
        );
        RecurringTaskPattern::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Daily Standup Notes'],
            ['task_list_id' => $sections[0]->id, 'frequency' => 'daily', 'next_run_date' => now()->addDay(), 'is_active' => true, 'created_by' => $this->users[6]->id, 'priority' => 'low']
        );

        // ── User capacities ─────────────────────────────────────────
        foreach ($this->users as $i => $user) {
            UserCapacity::firstOrCreate(
                ['project_id' => $project->id, 'user_id' => $user->id],
                ['weekly_capacity_hours' => $i === 0 ? 10 : ($i === 5 ? 20 : 35)]
            );
        }

        // ── Activity log entries ────────────────────────────────────
        foreach (array_slice($allTasks, 0, 10) as $task) {
            ProjectActivity::create(['project_task_id' => $task->id, 'user_id' => $this->users[rand(0, 5)]->id, 'type' => 'created', 'new_value' => $task->title]);
            if ($task->is_completed) {
                ProjectActivity::create(['project_task_id' => $task->id, 'user_id' => $task->assignee_id, 'type' => 'field_changed', 'field_name' => 'status', 'old_value' => 'open', 'new_value' => 'completed']);
            }
        }

        $this->command->info('  Fixed project "E-Commerce Platform Redesign" seeded with ' . count($allTasks) . ' tasks.');
    }

    private function seedBillingProject(): void
    {
        $project = Project::firstOrCreate(
            ['organization_id' => $this->org->id, 'name' => 'SaaS Dashboard Development'],
            [
                'owner_id'      => $this->users[0]->id,
                'client_id'     => $this->clients[1]->id,
                'description'   => 'Build a comprehensive analytics dashboard for StartupX SaaS product. Real-time metrics, user analytics, revenue tracking, and custom report builder.',
                'status'        => 'in_progress',
                'priority'      => 'critical',
                'color'         => '#F97316',
                'start_date'    => now()->subWeeks(6),
                'end_date'      => now()->addWeeks(10),
                'visibility'    => 'organization',
                'project_type'  => 'billing',
                'budget'        => 800000,
                'hourly_rate'   => 3000,
                'srs_url'       => 'https://docs.google.com/document/d/srs-saas-dashboard',
                'design_url'    => 'https://figma.com/file/saas-dashboard',
                'design_status' => 'approved',
                'design_approved_by' => $this->users[3]->id,
                'design_approved_at' => now()->subWeeks(5),
            ]
        );

        foreach ($this->users as $i => $user) {
            if (!$project->members()->where('user_id', $user->id)->exists()) {
                $project->members()->attach($user->id, ['role' => $i === 0 ? 'manager' : 'member']);
            }
        }

        $statuses = $project->statuses;
        $openStatus = $statuses->firstWhere('slug', 'open');
        $inProgressStatus = $statuses->firstWhere('slug', 'in_progress');
        $completedStatus = $statuses->firstWhere('slug', 'completed');

        // Labels
        $labels = [];
        foreach ([['Analytics', '#3B82F6'], ['Charts', '#10B981'], ['API', '#8B5CF6'], ['Critical Path', '#EF4444'], ['Revenue', '#F59E0B']] as [$n, $c]) {
            $labels[] = ProjectLabel::firstOrCreate(['project_id' => $project->id, 'name' => $n], ['color' => $c]);
        }

        // Milestones
        $m1 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'MVP Dashboard'], ['due_date' => now()->addWeeks(2), 'status' => 'open']);
        $m2 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Advanced Analytics'], ['due_date' => now()->addWeeks(6), 'status' => 'open']);
        $m3 = Milestone::firstOrCreate(['project_id' => $project->id, 'name' => 'Report Builder'], ['due_date' => now()->addWeeks(10), 'status' => 'open']);

        $sections = [];
        foreach (['Dashboard Widgets', 'User Analytics', 'Revenue Tracking', 'Report Builder', 'Infrastructure'] as $i => $name) {
            $sections[] = TaskList::firstOrCreate(['project_id' => $project->id, 'name' => $name], ['position' => ($i + 1) * 1000]);
        }

        if ($project->tasks()->count() > 5) {
            $this->command->info('  Billing project already has tasks, skipping task creation.');
            return;
        }

        $allTasks = [];
        $taskDefs = [
            [$sections[0]->id, 'KPI cards (MRR, ARR, Churn, LTV)',       'task',  'critical', $completedStatus, $m1, $this->users[1], 12, true],
            [$sections[0]->id, 'Real-time active users widget',           'task',  'high',     $completedStatus, $m1, $this->users[1], 8,  true],
            [$sections[0]->id, 'Revenue trend line chart',                'task',  'high',     $inProgressStatus,$m1, $this->users[1], 10, false],
            [$sections[0]->id, 'Subscription funnel visualization',       'story', 'medium',   $openStatus,      $m1, $this->users[2], 8,  false],
            [$sections[0]->id, 'Widget drag-and-drop customization',      'epic',  'medium',   $openStatus,      $m2, $this->users[1], 16, false],
            [$sections[1]->id, 'User segmentation engine',                'task',  'high',     $inProgressStatus,$m1, $this->users[2], 14, false],
            [$sections[1]->id, 'Cohort analysis charts',                  'task',  'medium',   $openStatus,      $m2, $this->users[1], 12, false],
            [$sections[1]->id, 'User journey mapping',                    'story', 'medium',   $openStatus,      $m2, $this->users[3], 10, false],
            [$sections[1]->id, 'Session replay integration',              'story', 'low',      $openStatus,      $m3, $this->users[2], 16, false],
            [$sections[2]->id, 'Stripe webhook processor',                'task',  'critical', $completedStatus, $m1, $this->users[2], 10, true],
            [$sections[2]->id, 'MRR calculation engine',                  'task',  'critical', $inProgressStatus,$m1, $this->users[2], 12, false],
            [$sections[2]->id, 'Revenue breakdown by plan/country',       'task',  'high',     $openStatus,      $m2, $this->users[1], 8,  false],
            [$sections[2]->id, 'Invoice & payment history',               'task',  'medium',   $openStatus,      $m2, $this->users[2], 10, false],
            [$sections[2]->id, 'Churn prediction model',                  'epic',  'high',     $openStatus,      $m3, $this->users[2], 20, false],
            [$sections[3]->id, 'Report template system',                  'task',  'high',     $openStatus,      $m3, $this->users[1], 16, false],
            [$sections[3]->id, 'Drag-and-drop report builder UI',         'epic',  'high',     $openStatus,      $m3, $this->users[1], 20, false],
            [$sections[3]->id, 'PDF/CSV export engine',                   'task',  'medium',   $openStatus,      $m3, $this->users[2], 8,  false],
            [$sections[3]->id, 'Scheduled report delivery (email)',       'task',  'medium',   $openStatus,      $m3, $this->users[4], 6,  false],
            [$sections[4]->id, 'ClickHouse analytics database setup',     'task',  'critical', $completedStatus, $m1, $this->users[4], 8,  true],
            [$sections[4]->id, 'Real-time event pipeline (Kafka)',        'task',  'high',     $inProgressStatus,$m1, $this->users[4], 14, false],
            [$sections[4]->id, 'CDN & asset optimization',                'task',  'medium',   $openStatus,      $m2, $this->users[4], 4,  false],
            [$sections[4]->id, 'Data retention & archival policy',        'task',  'low',      $openStatus,      $m3, $this->users[4], 6,  false],
        ];

        foreach ($taskDefs as $i => $td) {
            $task = ProjectTask::create([
                'project_id'        => $project->id,
                'task_list_id'      => $td[0],
                'title'             => $td[1],
                'description'       => 'Implementation details for: ' . $td[1],
                'issue_type'        => $td[2],
                'priority'          => $td[3],
                'project_status_id' => $td[4]?->id,
                'status'            => $td[4]?->slug ?? 'open',
                'milestone_id'      => $td[5]?->id,
                'assignee_id'       => $td[6]->id,
                'estimated_hours'   => $td[7],
                'is_completed'      => $td[8],
                'completed_at'      => $td[8] ? now()->subDays(rand(1, 20)) : null,
                'start_date'        => $td[8] ? now()->subWeeks(rand(3, 6)) : ($td[4] === $inProgressStatus ? now()->subWeeks(rand(1, 3)) : null),
                'due_date'          => now()->addWeeks(rand(1, 8)),
                'position'          => ($i + 1) * 1000,
                'story_points'      => rand(2, 13),
            ]);
            $allTasks[] = $task;
            $task->labels()->sync(array_rand(array_flip(collect($labels)->pluck('id')->toArray()), rand(1, 2)));
        }

        // Time logs for billing project
        foreach ($allTasks as $task) {
            $logCount = $task->is_completed ? rand(4, 8) : rand(0, 4);
            for ($l = 0; $l < $logCount; $l++) {
                ProjectTimeLog::create([
                    'project_task_id' => $task->id,
                    'user_id'         => $task->assignee_id ?? $this->users[rand(1, 5)]->id,
                    'hours'           => round(rand(1, 8) + rand(0, 3) * 0.25, 2),
                    'notes'           => ['Development', 'Research', 'Architecture', 'Testing', 'Client call', 'Sprint planning'][rand(0, 5)],
                    'logged_at'       => now()->subDays(rand(0, 40)),
                    'is_billable'     => true,
                ]);
            }
        }

        // ── Billing weeks ───────────────────────────────────────────
        for ($w = 4; $w >= 1; $w--) {
            $weekStart = now()->subWeeks($w)->startOfWeek();
            $weekEnd   = $weekStart->copy()->endOfWeek();

            $bw = ProjectBillingWeek::firstOrCreate(
                ['project_id' => $project->id, 'week_start' => $weekStart->toDateString()],
                [
                    'week_end'           => $weekEnd->toDateString(),
                    'total_actual_hours' => rand(30, 60),
                    'total_billable_hours'=> rand(25, 55),
                    'total_amount'       => rand(75000, 165000),
                    'locked_by'          => $w > 1 ? $this->users[0]->id : null,
                    'locked_at'          => $w > 1 ? now()->subWeeks($w - 1) : null,
                ]
            );

            foreach (array_slice($this->users, 1, 5) as $user) {
                $actual = round(rand(4, 16) + rand(0, 3) * 0.25, 2);
                ProjectBillingEntry::firstOrCreate(
                    ['billing_week_id' => $bw->id, 'user_id' => $user->id],
                    ['actual_hours' => $actual, 'billable_hours' => round($actual * 0.9, 2)]
                );
            }
        }

        // Comments, checklists, scope changes, updates for billing project
        foreach (array_slice($allTasks, 0, 8) as $task) {
            ProjectComment::create(['project_task_id' => $task->id, 'user_id' => $this->users[rand(0, 5)]->id, 'content' => 'Progress update: This is moving along well. Should be done on schedule.']);
            $cl = ProjectTaskChecklist::create(['project_task_id' => $task->id, 'name' => 'Definition of Done', 'position' => 1]);
            foreach (['Implementation complete', 'Tests passing', 'PR reviewed', 'Deployed to staging'] as $j => $item) {
                ProjectTaskChecklistItem::create(['project_task_checklist_id' => $cl->id, 'content' => $item, 'is_checked' => $task->is_completed || ($j < 2 && rand(0, 1)), 'position' => ($j + 1) * 1000]);
            }
        }

        ProjectScopeChange::firstOrCreate(
            ['project_id' => $project->id, 'title' => 'Add white-label support'],
            ['requested_by' => $this->users[6]->id, 'description' => 'Client wants to offer dashboard as white-label to their B2B customers.', 'type' => 'addition', 'cost_impact' => 120000, 'days_impact' => 15, 'status' => 'pending']
        );

        ProjectWeeklyUpdate::firstOrCreate(
            ['project_id' => $project->id, 'week_start' => now()->subWeeks(1)->startOfWeek()],
            ['created_by' => $this->users[6]->id, 'title' => 'SaaS Dashboard - Week 5', 'period_type' => 'weekly', 'week_end' => now()->subWeeks(1)->endOfWeek(), 'summary' => 'KPI widgets shipped. Real-time pipeline 70% done. Stripe webhooks working.', 'next_steps' => 'Complete MRR engine, start user segmentation', 'blockers' => 'ClickHouse cluster needs scaling - DevOps working on it']
        );

        foreach ($this->users as $user) {
            UserCapacity::firstOrCreate(['project_id' => $project->id, 'user_id' => $user->id], ['weekly_capacity_hours' => 30]);
        }

        $this->command->info('  Billing project "SaaS Dashboard Development" seeded with ' . count($allTasks) . ' tasks.');
    }
}
