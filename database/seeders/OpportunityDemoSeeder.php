<?php

namespace Database\Seeders;

use App\Models\OppActivityLog;
use App\Models\OppComment;
use App\Models\OppGoal;
use App\Models\OppPortfolio;
use App\Models\OppProject;
use App\Models\OppSection;
use App\Models\OppTag;
use App\Models\OppTask;
use App\Models\OppTaskDependency;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunityDemoSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::firstOrFail();
        $owner = User::find($org->owner_id);
        $members = $org->members()->take(8)->get();
        if ($members->count() < 3) {
            $this->command->warn('Need at least 3 org members. Run DemoProjectSeeder first.');
            return;
        }

        // ── Clean existing Opportunity data ──────────────────────────
        $this->command->info('Cleaning existing Opportunity data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        OppActivityLog::truncate();
        OppComment::query()->forceDelete();
        DB::table('opp_task_tags')->truncate();
        DB::table('opp_task_assignees')->truncate();
        DB::table('opp_task_followers')->truncate();
        DB::table('opp_task_dependencies')->truncate();
        DB::table('opp_task_likes')->truncate();
        DB::table('opp_task_custom_field_values')->truncate();
        DB::table('opp_project_custom_fields')->truncate();
        DB::table('opp_custom_fields')->truncate();
        DB::table('opp_attachments')->truncate();
        DB::table('opp_project_members')->truncate();
        OppTask::query()->forceDelete();
        OppSection::truncate();
        OppProject::query()->forceDelete();
        OppTag::truncate();
        DB::table('opp_portfolio_projects')->truncate();
        OppPortfolio::truncate();
        OppGoal::query()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Tags ─────────────────────────────────────────────────────
        $tags = [];
        foreach ([
            ['Bug', '#EF4444'], ['Feature', '#3B82F6'], ['Design', '#EC4899'],
            ['Backend', '#10B981'], ['Frontend', '#F59E0B'], ['Urgent', '#F43F5E'],
            ['Documentation', '#6B7280'], ['DevOps', '#8B5CF6'],
        ] as [$name, $color]) {
            $tags[$name] = OppTag::create(['organization_id' => $org->id, 'name' => $name, 'color' => $color]);
        }

        // ═══════════════════════════════════════════════════════════════
        // PROJECT 1: Mobile Banking App
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('Creating Project 1: Mobile Banking App...');

        $p1 = OppProject::create([
            'organization_id' => $org->id, 'owner_id' => $owner->id,
            'name' => 'Mobile Banking App', 'description' => 'Design and develop a full-featured mobile banking application with account management, payments, bill splitting, and financial insights. Target launch: Q2 2026.',
            'color' => '#3B82F6', 'status' => 'on_track',
            'start_date' => now()->subWeeks(4), 'due_date' => now()->addWeeks(12),
        ]);

        // Members
        foreach ($members->take(6) as $i => $m) {
            $p1->members()->attach($m->id, ['role' => $i === 0 ? 'owner' : 'editor']);
        }

        // Sections
        $s1_design   = OppSection::create(['project_id' => $p1->id, 'name' => 'Design & Research', 'position' => 1000]);
        $s1_auth     = OppSection::create(['project_id' => $p1->id, 'name' => 'Authentication & Security', 'position' => 2000]);
        $s1_core     = OppSection::create(['project_id' => $p1->id, 'name' => 'Core Banking Features', 'position' => 3000]);
        $s1_payments = OppSection::create(['project_id' => $p1->id, 'name' => 'Payments & Transfers', 'position' => 4000]);
        $s1_launch   = OppSection::create(['project_id' => $p1->id, 'name' => 'QA & Launch', 'position' => 5000]);

        $p1Tasks = [];
        $taskDefs1 = [
            // Design & Research
            [$s1_design, 'Competitive analysis of banking apps', 1, 'complete', now()->subWeeks(4), now()->subWeeks(3), ['Documentation']],
            [$s1_design, 'User persona research and interviews', 1, 'complete', now()->subWeeks(3), now()->subWeeks(2), ['Design']],
            [$s1_design, 'Create wireframes for all screens', 3, 'complete', now()->subWeeks(3), now()->subWeeks(1), ['Design']],
            [$s1_design, 'High-fidelity mockups in Figma', 3, 'incomplete', now()->subWeeks(1), now()->addDays(3), ['Design', 'Frontend']],
            [$s1_design, 'Design system and component library', 3, 'incomplete', now()->subWeeks(1), now()->addDays(5), ['Design']],
            [$s1_design, 'User testing with prototype', 3, 'incomplete', null, now()->addWeeks(2), ['Design']],

            // Authentication
            [$s1_auth, 'Implement biometric authentication (Face ID / fingerprint)', 2, 'incomplete', now()->subDays(3), now()->addDays(4), ['Backend', 'Feature']],
            [$s1_auth, 'Two-factor authentication via SMS/email', 2, 'incomplete', null, now()->addDays(7), ['Backend']],
            [$s1_auth, 'OAuth integration with Google and Apple Sign-In', 4, 'incomplete', null, now()->addWeeks(2), ['Backend']],
            [$s1_auth, 'Session management and token refresh', 2, 'incomplete', null, now()->addWeeks(2), ['Backend']],
            [$s1_auth, 'Security audit and penetration testing', 5, 'incomplete', null, now()->addWeeks(8), ['DevOps']],

            // Core Banking
            [$s1_core, 'Account dashboard with balance overview', 1, 'complete', now()->subWeeks(2), now()->subDays(5), ['Frontend', 'Feature']],
            [$s1_core, 'Transaction history with search and filters', 2, 'incomplete', now()->subDays(5), now()->addDays(2), ['Frontend', 'Backend']],
            [$s1_core, 'Spending categories and monthly breakdown', 2, 'incomplete', null, now()->addWeeks(1), ['Frontend']],
            [$s1_core, 'Savings goals tracker', 4, 'incomplete', null, now()->addWeeks(3), ['Feature']],
            [$s1_core, 'Card management (freeze, limits, PIN change)', 2, 'incomplete', null, now()->addWeeks(3), ['Backend', 'Feature']],
            [$s1_core, 'Push notifications for transactions', 4, 'incomplete', null, now()->addWeeks(4), ['Backend', 'DevOps']],
            [$s1_core, 'Monthly statement PDF generation', 5, 'incomplete', null, now()->addWeeks(5), ['Backend']],

            // Payments
            [$s1_payments, 'UPI payment integration', 2, 'incomplete', now()->subDays(2), now()->addDays(5), ['Backend', 'Urgent']],
            [$s1_payments, 'Bank-to-bank NEFT/RTGS transfers', 2, 'incomplete', null, now()->addWeeks(2), ['Backend']],
            [$s1_payments, 'Bill payment system (electricity, mobile, DTH)', 4, 'incomplete', null, now()->addWeeks(4), ['Feature']],
            [$s1_payments, 'QR code scanner for merchant payments', 4, 'incomplete', null, now()->addWeeks(5), ['Frontend', 'Feature']],
            [$s1_payments, 'Split bill feature with friends', 5, 'incomplete', null, now()->addWeeks(6), ['Feature']],
            [$s1_payments, 'Recurring payment scheduling', 5, 'incomplete', null, now()->addWeeks(7), ['Backend']],

            // QA & Launch
            [$s1_launch, 'Write unit tests for core banking APIs', 4, 'incomplete', null, now()->addWeeks(6), ['Backend']],
            [$s1_launch, 'End-to-end testing on iOS and Android', 5, 'incomplete', null, now()->addWeeks(8), ['DevOps']],
            [$s1_launch, 'Performance load testing (10K concurrent users)', 5, 'incomplete', null, now()->addWeeks(9), ['DevOps']],
            [$s1_launch, 'RBI compliance review', 5, 'incomplete', null, now()->addWeeks(10), ['Documentation']],
            [$s1_launch, 'App Store and Play Store submission', 5, 'incomplete', null, now()->addWeeks(11), ['DevOps']],
            [$s1_launch, 'Launch marketing campaign', 5, 'incomplete', null, now()->addWeeks(12), ['Documentation']],
        ];

        foreach ($taskDefs1 as $i => [$section, $title, $assigneeIdx, $status, $start, $due, $tagNames]) {
            $assignee = $members[$assigneeIdx % $members->count()];
            $task = OppTask::create([
                'project_id' => $p1->id, 'section_id' => $section->id, 'title' => $title,
                'assignee_id' => $assignee->id, 'status' => $status, 'position' => ($i + 1) * 1000,
                'start_date' => $start, 'due_date' => $due, 'created_by' => $owner->id,
                'completed_at' => $status === 'complete' ? ($due ?? now()->subDays(rand(1, 7))) : null,
                'completed_by' => $status === 'complete' ? $assignee->id : null,
            ]);
            $p1Tasks[] = $task;

            // Attach tags
            $tagIds = collect($tagNames)->map(fn($n) => $tags[$n]->id)->all();
            $task->tags()->sync($tagIds);
        }

        // Subtasks for key tasks
        foreach ([0, 3, 6, 11, 18] as $parentIdx) {
            $parent = $p1Tasks[$parentIdx];
            $subs = [
                ['Research top 5 competitors', 'Define evaluation criteria', 'Create comparison matrix'],
                ['Mobile breakpoint layouts', 'Dark mode variants', 'Accessibility review'],
                ['Integrate SDK', 'Error handling flow', 'Fallback to PIN'],
                ['Real-time balance API', 'Chart visualization', 'Multi-account toggle'],
                ['Payment gateway setup', 'Transaction validation', 'Receipt generation'],
            ];
            foreach ($subs[array_search($parentIdx, [0, 3, 6, 11, 18])] as $j => $subTitle) {
                OppTask::create([
                    'project_id' => $p1->id, 'parent_task_id' => $parent->id,
                    'title' => $subTitle, 'assignee_id' => $parent->assignee_id,
                    'status' => $parent->status, 'position' => ($j + 1) * 1000,
                    'created_by' => $owner->id,
                    'completed_at' => $parent->completed_at, 'completed_by' => $parent->completed_by,
                ]);
            }
        }

        // Dependencies
        OppTaskDependency::create(['task_id' => $p1Tasks[3]->id, 'depends_on_task_id' => $p1Tasks[2]->id, 'type' => 'blocking']);
        OppTaskDependency::create(['task_id' => $p1Tasks[5]->id, 'depends_on_task_id' => $p1Tasks[3]->id, 'type' => 'blocking']);
        OppTaskDependency::create(['task_id' => $p1Tasks[24]->id, 'depends_on_task_id' => $p1Tasks[18]->id, 'type' => 'blocking']);

        // Comments
        $comments1 = [
            [$p1Tasks[3], $members[3], 'The mockups for the dashboard screen are ready for review. I used the new brand colors from the style guide.'],
            [$p1Tasks[3], $owner, 'Looks great! Can we add a quick-actions section at the top? Users should be able to send money and pay bills from the home screen.'],
            [$p1Tasks[6], $members[2], 'Face ID integration is working on iOS. Android fingerprint API has some edge cases on Samsung devices that I need to handle.'],
            [$p1Tasks[12], $members[2], 'Transaction history API is returning results in 120ms for 10K records. Added pagination and infinite scroll on the frontend.'],
            [$p1Tasks[12], $members[1], 'Can we add date range filters? Users will want to search transactions by month.'],
            [$p1Tasks[18], $members[2], 'UPI integration is progressing well. Razorpay sandbox is configured. Need production credentials from the bank team.'],
            [$p1Tasks[18], $owner, 'I have escalated the credential request. Should get it by Wednesday. Mark this as blocked until then.'],
        ];
        foreach ($comments1 as [$task, $user, $body]) {
            OppComment::create(['task_id' => $task->id, 'user_id' => $user->id, 'body' => $body]);
        }

        // Activity log
        foreach ($p1Tasks as $task) {
            OppActivityLog::create(['task_id' => $task->id, 'project_id' => $p1->id, 'user_id' => $owner->id, 'action' => 'task.created', 'created_at' => $task->created_at]);
            if ($task->status === 'complete') {
                OppActivityLog::create(['task_id' => $task->id, 'project_id' => $p1->id, 'user_id' => $task->assignee_id, 'action' => 'task.completed', 'field_name' => 'status', 'old_value' => 'incomplete', 'new_value' => 'complete', 'created_at' => $task->completed_at]);
            }
        }

        // ═══════════════════════════════════════════════════════════════
        // PROJECT 2: Company Website Revamp
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('Creating Project 2: Company Website Revamp...');

        $p2 = OppProject::create([
            'organization_id' => $org->id, 'owner_id' => $members[6]->id,
            'name' => 'Company Website Revamp', 'description' => 'Complete redesign and rebuild of the corporate website. New brand identity, improved SEO, blog migration, careers page, and conversion-optimized landing pages.',
            'color' => '#8B5CF6', 'status' => 'at_risk',
            'start_date' => now()->subWeeks(6), 'due_date' => now()->addWeeks(6),
        ]);

        foreach ($members->take(5) as $i => $m) {
            $p2->members()->attach($m->id, ['role' => $i === 0 ? 'owner' : 'editor']);
        }

        $s2_content  = OppSection::create(['project_id' => $p2->id, 'name' => 'Content & Strategy', 'position' => 1000]);
        $s2_design   = OppSection::create(['project_id' => $p2->id, 'name' => 'Design', 'position' => 2000]);
        $s2_dev      = OppSection::create(['project_id' => $p2->id, 'name' => 'Development', 'position' => 3000]);
        $s2_seo      = OppSection::create(['project_id' => $p2->id, 'name' => 'SEO & Analytics', 'position' => 4000]);
        $s2_launch   = OppSection::create(['project_id' => $p2->id, 'name' => 'Launch', 'position' => 5000]);

        $p2Tasks = [];
        $taskDefs2 = [
            // Content
            [$s2_content, 'Audit existing website content', 0, 'complete', now()->subWeeks(6), now()->subWeeks(5), ['Documentation']],
            [$s2_content, 'Define new information architecture', 6, 'complete', now()->subWeeks(5), now()->subWeeks(4), ['Documentation']],
            [$s2_content, 'Write copy for homepage and about page', 6, 'complete', now()->subWeeks(4), now()->subWeeks(2), ['Documentation']],
            [$s2_content, 'Write copy for services pages (5 pages)', 6, 'incomplete', now()->subWeeks(2), now()->subDays(2), ['Documentation', 'Urgent']],
            [$s2_content, 'Create case studies (3 client stories)', 0, 'incomplete', null, now()->addWeeks(1), ['Documentation']],
            [$s2_content, 'Photography and video assets', 3, 'incomplete', null, now()->addWeeks(2), ['Design']],
            [$s2_content, 'Write blog posts for launch (5 articles)', 6, 'incomplete', null, now()->addWeeks(3), ['Documentation']],

            // Design
            [$s2_design, 'Brand identity refresh (logo, colors, typography)', 3, 'complete', now()->subWeeks(5), now()->subWeeks(3), ['Design']],
            [$s2_design, 'Homepage design', 3, 'complete', now()->subWeeks(3), now()->subWeeks(1), ['Design']],
            [$s2_design, 'Inner page templates design', 3, 'incomplete', now()->subWeeks(1), now()->addDays(3), ['Design']],
            [$s2_design, 'Blog page and single post design', 3, 'incomplete', null, now()->addDays(5), ['Design']],
            [$s2_design, 'Careers page with job listing design', 3, 'incomplete', null, now()->addWeeks(1), ['Design']],
            [$s2_design, 'Contact page with office map', 3, 'incomplete', null, now()->addWeeks(1), ['Design']],
            [$s2_design, 'Mobile responsive review all pages', 3, 'incomplete', null, now()->addWeeks(2), ['Design', 'Frontend']],

            // Development
            [$s2_dev, 'Set up Next.js project with Tailwind CSS', 1, 'complete', now()->subWeeks(3), now()->subWeeks(2), ['Frontend', 'DevOps']],
            [$s2_dev, 'Implement homepage with animations', 1, 'incomplete', now()->subWeeks(1), now()->addDays(2), ['Frontend']],
            [$s2_dev, 'Build reusable component library', 1, 'incomplete', now()->subDays(3), now()->addDays(4), ['Frontend']],
            [$s2_dev, 'CMS integration (Sanity or Strapi)', 2, 'incomplete', null, now()->addWeeks(1), ['Backend']],
            [$s2_dev, 'Blog with MDX support', 2, 'incomplete', null, now()->addWeeks(2), ['Backend', 'Frontend']],
            [$s2_dev, 'Careers page with Greenhouse API integration', 2, 'incomplete', null, now()->addWeeks(2), ['Backend']],
            [$s2_dev, 'Contact form with email notifications', 4, 'incomplete', null, now()->addWeeks(3), ['Backend']],
            [$s2_dev, 'Newsletter signup with Mailchimp integration', 4, 'incomplete', null, now()->addWeeks(3), ['Backend']],

            // SEO
            [$s2_seo, 'Keyword research for all pages', 0, 'complete', now()->subWeeks(5), now()->subWeeks(4), ['Documentation']],
            [$s2_seo, 'Meta tags and Open Graph setup', 1, 'incomplete', null, now()->addWeeks(2), ['Frontend']],
            [$s2_seo, 'Schema markup implementation', 1, 'incomplete', null, now()->addWeeks(3), ['Frontend']],
            [$s2_seo, 'Google Analytics 4 and Tag Manager setup', 4, 'incomplete', null, now()->addWeeks(3), ['DevOps']],
            [$s2_seo, 'Sitemap and robots.txt configuration', 1, 'incomplete', null, now()->addWeeks(4), ['DevOps']],
            [$s2_seo, '301 redirect mapping from old URLs', 4, 'incomplete', null, now()->addWeeks(4), ['Backend']],

            // Launch
            [$s2_launch, 'Cross-browser testing (Chrome, Safari, Firefox, Edge)', 5, 'incomplete', null, now()->addWeeks(4), ['Frontend']],
            [$s2_launch, 'Performance optimization (Core Web Vitals)', 1, 'incomplete', null, now()->addWeeks(4), ['Frontend', 'DevOps']],
            [$s2_launch, 'SSL certificate and CDN setup', 4, 'incomplete', null, now()->addWeeks(5), ['DevOps']],
            [$s2_launch, 'DNS migration and go-live', 4, 'incomplete', null, now()->addWeeks(5), ['DevOps', 'Urgent']],
            [$s2_launch, 'Post-launch monitoring (48 hours)', 4, 'incomplete', null, now()->addWeeks(6), ['DevOps']],
        ];

        foreach ($taskDefs2 as $i => [$section, $title, $assigneeIdx, $status, $start, $due, $tagNames]) {
            $assignee = $members[$assigneeIdx % $members->count()];
            $task = OppTask::create([
                'project_id' => $p2->id, 'section_id' => $section->id, 'title' => $title,
                'assignee_id' => $assignee->id, 'status' => $status, 'position' => ($i + 1) * 1000,
                'start_date' => $start, 'due_date' => $due, 'created_by' => $members[6]->id,
                'completed_at' => $status === 'complete' ? ($due ?? now()->subDays(rand(1, 10))) : null,
                'completed_by' => $status === 'complete' ? $assignee->id : null,
            ]);
            $p2Tasks[] = $task;
            $task->tags()->sync(collect($tagNames)->map(fn($n) => $tags[$n]->id)->all());
        }

        // Comments for project 2
        $comments2 = [
            [$p2Tasks[3], $members[6], 'Services page copy is overdue. We need to prioritize this — the design team is blocked waiting for content.'],
            [$p2Tasks[3], $members[0], 'Working on it today. Will have the first draft by EOD.'],
            [$p2Tasks[8], $members[3], 'Homepage design approved by the CEO. Uploading the final Figma file now.'],
            [$p2Tasks[15], $members[1], 'Homepage animations are looking smooth. Used Framer Motion for the hero section and scroll-triggered reveals.'],
            [$p2Tasks[15], $members[3], 'Love the animations! Can we add a subtle parallax effect on the testimonials section?'],
            [$p2Tasks[17], $members[2], 'Evaluating Sanity vs Strapi. Sanity has better real-time collaboration but Strapi is open source. Leaning towards Sanity.'],
        ];
        foreach ($comments2 as [$task, $user, $body]) {
            OppComment::create(['task_id' => $task->id, 'user_id' => $user->id, 'body' => $body]);
        }

        foreach ($p2Tasks as $task) {
            OppActivityLog::create(['task_id' => $task->id, 'project_id' => $p2->id, 'user_id' => $members[6]->id, 'action' => 'task.created', 'created_at' => $task->created_at]);
            if ($task->status === 'complete') {
                OppActivityLog::create(['task_id' => $task->id, 'project_id' => $p2->id, 'user_id' => $task->assignee_id, 'action' => 'task.completed', 'field_name' => 'status', 'old_value' => 'incomplete', 'new_value' => 'complete', 'created_at' => $task->completed_at]);
            }
        }

        // ── Portfolio ────────────────────────────────────────────────
        $portfolio = OppPortfolio::create(['organization_id' => $org->id, 'owner_id' => $owner->id, 'name' => 'Q2 2026 Initiatives', 'color' => '#3B82F6']);
        $portfolio->projects()->attach([$p1->id, $p2->id]);

        // ── Goals ────────────────────────────────────────────────────
        $g1 = OppGoal::create([
            'organization_id' => $org->id, 'owner_id' => $owner->id,
            'title' => 'Launch mobile banking app by June 2026', 'goal_type' => 'company',
            'metric_type' => 'percentage', 'target_value' => 100, 'current_value' => 25,
            'status' => 'on_track', 'due_date' => now()->addWeeks(12),
        ]);
        OppGoal::create([
            'organization_id' => $org->id, 'owner_id' => $members[6]->id, 'parent_id' => $g1->id,
            'title' => 'Complete website revamp before app launch', 'goal_type' => 'team',
            'metric_type' => 'percentage', 'target_value' => 100, 'current_value' => 40,
            'status' => 'at_risk', 'due_date' => now()->addWeeks(6),
        ]);

        // ── Summary ──────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('=== Opportunity Demo Data Seeded ===');
        $this->command->info("Project 1: {$p1->name} — " . count($p1Tasks) . ' tasks + subtasks');
        $this->command->info("Project 2: {$p2->name} — " . count($p2Tasks) . ' tasks');
        $this->command->info('Tags: ' . count($tags));
        $this->command->info('Portfolio: Q2 2026 Initiatives (2 projects)');
        $this->command->info('Goals: 2 (parent + child)');
        $this->command->info('Total tasks: ' . OppTask::count());
        $this->command->info('Total comments: ' . OppComment::count());
    }
}
