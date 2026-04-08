<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $boardProduct = Product::where('key', 'board')->first();
        $projectsProduct = Product::where('key', 'projects')->first();
        $opportunityProduct = Product::where('key', 'opportunity')->first();
        $hrProduct = Product::where('key', 'hr')->first();
        $knowledgeProduct = Product::where('key', 'knowledge_base')->first();
        $docsProduct = Product::where('key', 'docs')->first();

        $permissions = [
            // ── Global (no product) ───────────────────────────────────
            ['key' => 'org.settings.view',     'name' => 'View Organization Settings',  'group' => 'organization', 'product_id' => null],
            ['key' => 'org.settings.edit',     'name' => 'Edit Organization Settings',  'group' => 'organization', 'product_id' => null],
            ['key' => 'org.members.view',      'name' => 'View Members',                'group' => 'organization', 'product_id' => null],
            ['key' => 'org.members.invite',    'name' => 'Invite Members',              'group' => 'organization', 'product_id' => null],
            ['key' => 'org.members.edit',      'name' => 'Edit Members',                'group' => 'organization', 'product_id' => null],
            ['key' => 'org.members.remove',    'name' => 'Remove Members',              'group' => 'organization', 'product_id' => null],
            ['key' => 'org.billing.view',      'name' => 'View Billing',                'group' => 'organization', 'product_id' => null],
            ['key' => 'org.billing.manage',    'name' => 'Manage Billing',              'group' => 'organization', 'product_id' => null],
            ['key' => 'admin.roles.manage',    'name' => 'Manage Roles & Permissions',  'group' => 'admin',        'product_id' => null],
            ['key' => 'admin.users.manage',    'name' => 'Manage Users',                'group' => 'admin',        'product_id' => null],
            ['key' => 'admin.users.view',      'name' => 'View User Profiles',          'group' => 'admin',        'product_id' => null],
            ['key' => 'org.clients.view',      'name' => 'View Clients & Client Portal', 'group' => 'clients_crm',  'product_id' => null],
            ['key' => 'org.clients.manage',    'name' => 'Manage Clients & Portal',     'group' => 'clients_crm',  'product_id' => null],

            // ── BAI Board ─────────────────────────────────────────────
            ['key' => 'board.boards.view',     'name' => 'View Boards',        'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.boards.create',   'name' => 'Create Boards',      'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.boards.edit',     'name' => 'Edit Boards',        'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.boards.delete',   'name' => 'Delete Boards',      'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.boards.archive',  'name' => 'Archive Boards',     'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.members.manage',  'name' => 'Manage Board Members', 'group' => 'boards',  'product_id' => $boardProduct?->id],
            ['key' => 'board.cards.create',    'name' => 'Create Cards',       'group' => 'cards',    'product_id' => $boardProduct?->id],
            ['key' => 'board.cards.edit',      'name' => 'Edit Cards',         'group' => 'cards',    'product_id' => $boardProduct?->id],
            ['key' => 'board.cards.delete',    'name' => 'Delete Cards',       'group' => 'cards',    'product_id' => $boardProduct?->id],
            ['key' => 'board.cards.move',      'name' => 'Move Cards',         'group' => 'cards',    'product_id' => $boardProduct?->id],
            ['key' => 'board.labels.manage',   'name' => 'Manage Board Labels', 'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.chat.access',     'name' => 'Access Board Chat',  'group' => 'boards',   'product_id' => $boardProduct?->id],

            // ── BAI Projects ──────────────────────────────────────────
            ['key' => 'projects.view',            'name' => 'View Projects',           'group' => 'projects',      'product_id' => $projectsProduct?->id],
            ['key' => 'projects.create',          'name' => 'Create Projects',         'group' => 'projects',      'product_id' => $projectsProduct?->id],
            ['key' => 'projects.edit',            'name' => 'Edit Projects',           'group' => 'projects',      'product_id' => $projectsProduct?->id],
            ['key' => 'projects.delete',          'name' => 'Delete Projects',         'group' => 'projects',      'product_id' => $projectsProduct?->id],
            ['key' => 'projects.members.manage',  'name' => 'Manage Project Members',  'group' => 'projects',     'product_id' => $projectsProduct?->id],
            ['key' => 'tasks.view',          'name' => 'View Tasks',         'group' => 'tasks',          'product_id' => $projectsProduct?->id],
            ['key' => 'tasks.create',        'name' => 'Create Tasks',       'group' => 'tasks',          'product_id' => $projectsProduct?->id],
            ['key' => 'tasks.edit',          'name' => 'Edit Tasks',         'group' => 'tasks',          'product_id' => $projectsProduct?->id],
            ['key' => 'tasks.delete',        'name' => 'Delete Tasks',       'group' => 'tasks',          'product_id' => $projectsProduct?->id],
            ['key' => 'tasks.assign',        'name' => 'Assign Tasks',       'group' => 'tasks',          'product_id' => $projectsProduct?->id],
            ['key' => 'tasks.bulk_actions',  'name' => 'Bulk Task Actions',  'group' => 'tasks',          'product_id' => $projectsProduct?->id],
            ['key' => 'time.log',                'name' => 'Log Time',              'group' => 'time_tracking', 'product_id' => $projectsProduct?->id],
            ['key' => 'time.view_own',           'name' => 'View Own Time Logs',    'group' => 'time_tracking', 'product_id' => $projectsProduct?->id],
            ['key' => 'time.view_all',           'name' => 'View All Time Logs',    'group' => 'time_tracking', 'product_id' => $projectsProduct?->id],
            ['key' => 'time.timesheets.submit',  'name' => 'Submit Timesheets',     'group' => 'time_tracking', 'product_id' => $projectsProduct?->id],
            ['key' => 'time.timesheets.approve', 'name' => 'Approve Timesheets',    'group' => 'time_tracking', 'product_id' => $projectsProduct?->id],
            ['key' => 'financial.budget.view',    'name' => 'View Budget',          'group' => 'financial',     'product_id' => $projectsProduct?->id],
            ['key' => 'financial.budget.manage',  'name' => 'Manage Budget',        'group' => 'financial',     'product_id' => $projectsProduct?->id],
            ['key' => 'financial.billing.view',   'name' => 'View Project Billing', 'group' => 'financial',     'product_id' => $projectsProduct?->id],
            ['key' => 'financial.billing.manage', 'name' => 'Manage Billing',       'group' => 'financial',     'product_id' => $projectsProduct?->id],
            ['key' => 'financial.reports.view',   'name' => 'View Reports',         'group' => 'financial',     'product_id' => $projectsProduct?->id],
            ['key' => 'content.documents.view',   'name' => 'View Documents',       'group' => 'content',       'product_id' => $projectsProduct?->id],
            ['key' => 'content.documents.manage', 'name' => 'Manage Documents',     'group' => 'content',       'product_id' => $projectsProduct?->id],
            ['key' => 'content.chat.access',      'name' => 'Access Project Chat',  'group' => 'content',       'product_id' => $projectsProduct?->id],
            ['key' => 'content.comments.create',  'name' => 'Create Comments',      'group' => 'content',       'product_id' => $projectsProduct?->id],

            // ── Opportunity ───────────────────────────────────────────
            ['key' => 'opp.projects.view',       'name' => 'View Opportunity Projects',    'group' => 'opp_projects', 'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.projects.create',     'name' => 'Create Opportunity Projects',  'group' => 'opp_projects', 'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.projects.edit',       'name' => 'Edit Opportunity Projects',    'group' => 'opp_projects', 'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.projects.delete',     'name' => 'Delete Opportunity Projects',  'group' => 'opp_projects', 'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.tasks.view',          'name' => 'View Opportunity Tasks',       'group' => 'opp_tasks',    'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.tasks.create',        'name' => 'Create Opportunity Tasks',     'group' => 'opp_tasks',    'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.tasks.edit',          'name' => 'Edit Opportunity Tasks',       'group' => 'opp_tasks',    'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.tasks.delete',        'name' => 'Delete Opportunity Tasks',     'group' => 'opp_tasks',    'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.tasks.assign',        'name' => 'Assign Opportunity Tasks',     'group' => 'opp_tasks',    'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.goals.manage',        'name' => 'Manage Goals',                 'group' => 'opp_goals',    'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.portfolios.manage',   'name' => 'Manage Portfolios',            'group' => 'opp_portfolios', 'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.reports.view',        'name' => 'View Opportunity Reports',     'group' => 'opp_reports',  'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.forms.manage',        'name' => 'Manage Opportunity Forms',     'group' => 'opp_forms',    'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.rules.manage',        'name' => 'Manage Automation Rules',      'group' => 'opp_rules',    'product_id' => $opportunityProduct?->id],
            ['key' => 'opp.templates.manage',    'name' => 'Manage Opportunity Templates', 'group' => 'opp_templates', 'product_id' => $opportunityProduct?->id],

            // ── BAI HR ────────────────────────────────────────────────
            ['key' => 'hr.people.view',              'name' => 'View People Directory',      'group' => 'hr_people',      'product_id' => $hrProduct?->id],
            ['key' => 'hr.people.edit',              'name' => 'Edit Employee Profiles',     'group' => 'hr_people',      'product_id' => $hrProduct?->id],
            ['key' => 'hr.departments.manage',       'name' => 'Manage Departments',         'group' => 'hr_departments', 'product_id' => $hrProduct?->id],
            ['key' => 'hr.attendance.view_own',      'name' => 'View Own Attendance',        'group' => 'hr_attendance',  'product_id' => $hrProduct?->id],
            ['key' => 'hr.attendance.view_all',      'name' => 'View All Attendance',        'group' => 'hr_attendance',  'product_id' => $hrProduct?->id],
            ['key' => 'hr.attendance.manage',        'name' => 'Manage Attendance',          'group' => 'hr_attendance',  'product_id' => $hrProduct?->id],
            ['key' => 'hr.leave.apply',              'name' => 'Apply for Leave',            'group' => 'hr_leave',       'product_id' => $hrProduct?->id],
            ['key' => 'hr.leave.view_own',           'name' => 'View Own Leave',             'group' => 'hr_leave',       'product_id' => $hrProduct?->id],
            ['key' => 'hr.leave.view_all',           'name' => 'View All Leave',             'group' => 'hr_leave',       'product_id' => $hrProduct?->id],
            ['key' => 'hr.leave.approve',            'name' => 'Approve/Reject Leave',       'group' => 'hr_leave',       'product_id' => $hrProduct?->id],
            ['key' => 'hr.payroll.view_own',         'name' => 'View Own Payslips',          'group' => 'hr_payroll',     'product_id' => $hrProduct?->id],
            ['key' => 'hr.payroll.view_all',         'name' => 'View All Payroll',           'group' => 'hr_payroll',     'product_id' => $hrProduct?->id],
            ['key' => 'hr.payroll.manage',           'name' => 'Manage Payroll',             'group' => 'hr_payroll',     'product_id' => $hrProduct?->id],
            ['key' => 'hr.payroll.process',          'name' => 'Process Payroll Runs',       'group' => 'hr_payroll',     'product_id' => $hrProduct?->id],
            ['key' => 'hr.salary.manage',            'name' => 'Manage Salary Structures',   'group' => 'hr_payroll',     'product_id' => $hrProduct?->id],
            ['key' => 'hr.performance.view_own',     'name' => 'View Own Reviews',           'group' => 'hr_performance', 'product_id' => $hrProduct?->id],
            ['key' => 'hr.performance.view_all',     'name' => 'View All Reviews',           'group' => 'hr_performance', 'product_id' => $hrProduct?->id],
            ['key' => 'hr.performance.manage',       'name' => 'Manage Review Cycles',       'group' => 'hr_performance', 'product_id' => $hrProduct?->id],
            ['key' => 'hr.expenses.submit',          'name' => 'Submit Expense Claims',      'group' => 'hr_expenses',   'product_id' => $hrProduct?->id],
            ['key' => 'hr.expenses.approve',         'name' => 'Approve Expense Claims',     'group' => 'hr_expenses',   'product_id' => $hrProduct?->id],
            ['key' => 'hr.expenses.view_all',        'name' => 'View All Expenses',          'group' => 'hr_expenses',   'product_id' => $hrProduct?->id],
            ['key' => 'hr.recruitment.view',         'name' => 'View Job Postings',          'group' => 'hr_recruitment', 'product_id' => $hrProduct?->id],
            ['key' => 'hr.recruitment.manage',       'name' => 'Manage Recruitment',         'group' => 'hr_recruitment', 'product_id' => $hrProduct?->id],
            ['key' => 'hr.surveys.manage',           'name' => 'Manage Surveys',             'group' => 'hr_surveys',    'product_id' => $hrProduct?->id],
            ['key' => 'hr.surveys.respond',          'name' => 'Respond to Surveys',         'group' => 'hr_surveys',    'product_id' => $hrProduct?->id],
            ['key' => 'hr.announcements.manage',     'name' => 'Manage Announcements',       'group' => 'hr_announcements', 'product_id' => $hrProduct?->id],
            ['key' => 'hr.announcements.view',       'name' => 'View Announcements',         'group' => 'hr_announcements', 'product_id' => $hrProduct?->id],

            // ── Knowledge Base ─────────────────────────────────────────
            ['key' => 'knowledge.view',       'name' => 'View Knowledge Base',       'group' => 'knowledge', 'product_id' => $knowledgeProduct?->id],
            ['key' => 'knowledge.contribute', 'name' => 'Create & Edit Own Articles', 'group' => 'knowledge', 'product_id' => $knowledgeProduct?->id],
            ['key' => 'knowledge.moderate',   'name' => 'Moderate Knowledge Base',   'group' => 'knowledge', 'product_id' => $knowledgeProduct?->id],

            // ── BAI Docs ──────────────────────────────────────────────
            ['key' => 'docs.view',     'name' => 'View Documents',              'group' => 'docs', 'product_id' => $docsProduct?->id],
            ['key' => 'docs.create',   'name' => 'Create & Edit Own Documents', 'group' => 'docs', 'product_id' => $docsProduct?->id],
            ['key' => 'docs.moderate', 'name' => 'Moderate All Documents',      'group' => 'docs', 'product_id' => $docsProduct?->id],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['key' => $perm['key']],
                $perm
            );
        }

        // Seed default roles for all existing organizations
        $allPermissionIds = Permission::pluck('id')->toArray();
        $memberPermissionKeys = self::memberPermissionKeys();
        $memberPermissionIds = Permission::whereIn('key', $memberPermissionKeys)->pluck('id')->toArray();

        Organization::all()->each(function (Organization $org) use ($allPermissionIds, $memberPermissionIds) {
            self::seedRolesForOrg($org, $allPermissionIds, $memberPermissionIds);
        });
    }

    public static function memberPermissionKeys(): array
    {
        return [
            // Global
            'org.members.view',
            // Board basics
            'board.boards.view', 'board.cards.create', 'board.cards.edit', 'board.cards.move', 'board.chat.access',
            // Project basics
            'projects.view', 'org.clients.view', 'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
            'time.log', 'time.view_own', 'time.timesheets.submit',
            'financial.budget.view', 'financial.reports.view',
            'content.documents.view', 'content.chat.access', 'content.comments.create',
            // Opportunity basics
            'opp.projects.view', 'opp.tasks.view', 'opp.tasks.create', 'opp.tasks.edit', 'opp.tasks.assign',
            'opp.reports.view',
            // HR basics
            'hr.people.view', 'hr.attendance.view_own', 'hr.leave.apply', 'hr.leave.view_own',
            'hr.payroll.view_own', 'hr.performance.view_own', 'hr.expenses.submit',
            'hr.surveys.respond', 'hr.announcements.view',
            // Knowledge Base
            'knowledge.view', 'knowledge.contribute',
            // BAI Docs
            'docs.view', 'docs.create',
        ];
    }

    public static function seedRolesForOrg(Organization $org, ?array $allPermissionIds = null, ?array $memberPermissionIds = null): void
    {
        if ($allPermissionIds === null) {
            $allPermissionIds = Permission::pluck('id')->toArray();
        }
        if ($memberPermissionIds === null) {
            $memberPermissionIds = Permission::whereIn('key', self::memberPermissionKeys())->pluck('id')->toArray();
        }

        // Update existing system roles' permissions (or create if missing)
        $ownerRole = Role::firstOrCreate(
            ['organization_id' => $org->id, 'slug' => 'owner'],
            ['name' => 'Owner', 'description' => 'Full access to everything', 'is_system' => true, 'level' => 100]
        );
        $ownerRole->permissions()->sync($allPermissionIds);

        $adminRole = Role::firstOrCreate(
            ['organization_id' => $org->id, 'slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'Administrative access', 'is_system' => true, 'level' => 90]
        );
        $adminRole->permissions()->sync($allPermissionIds);

        $memberRole = Role::firstOrCreate(
            ['organization_id' => $org->id, 'slug' => 'member'],
            ['name' => 'Member', 'description' => 'Standard member access', 'is_system' => true, 'level' => 10]
        );
        $memberRole->permissions()->sync($memberPermissionIds);
    }
}
