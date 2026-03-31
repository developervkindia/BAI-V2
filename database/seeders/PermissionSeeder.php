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
        $boardProduct    = Product::where('key', 'board')->first();
        $projectsProduct = Product::where('key', 'projects')->first();

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

            // ── SmartBoard ────────────────────────────────────────────
            ['key' => 'board.boards.view',     'name' => 'View Boards',        'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.boards.create',   'name' => 'Create Boards',      'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.boards.edit',     'name' => 'Edit Boards',        'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.boards.delete',   'name' => 'Delete Boards',      'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.boards.archive',  'name' => 'Archive Boards',     'group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.members.manage',  'name' => 'Manage Board Members','group' => 'boards',  'product_id' => $boardProduct?->id],
            ['key' => 'board.cards.create',    'name' => 'Create Cards',       'group' => 'cards',    'product_id' => $boardProduct?->id],
            ['key' => 'board.cards.edit',      'name' => 'Edit Cards',         'group' => 'cards',    'product_id' => $boardProduct?->id],
            ['key' => 'board.cards.delete',    'name' => 'Delete Cards',       'group' => 'cards',    'product_id' => $boardProduct?->id],
            ['key' => 'board.cards.move',      'name' => 'Move Cards',         'group' => 'cards',    'product_id' => $boardProduct?->id],
            ['key' => 'board.labels.manage',   'name' => 'Manage Board Labels','group' => 'boards',   'product_id' => $boardProduct?->id],
            ['key' => 'board.chat.access',     'name' => 'Access Board Chat',  'group' => 'boards',   'product_id' => $boardProduct?->id],

            // ── SmartProjects ─────────────────────────────────────────
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
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['key' => $perm['key']],
                $perm
            );
        }

        // Seed default roles for all existing organizations
        $allPermissionIds = Permission::pluck('id')->toArray();
        $memberPermissionKeys = [
            // Global
            'org.members.view',
            // Board basics
            'board.boards.view', 'board.cards.create', 'board.cards.edit', 'board.cards.move', 'board.chat.access',
            // Project basics
            'projects.view', 'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
            'time.log', 'time.view_own', 'time.timesheets.submit',
            'financial.budget.view', 'financial.reports.view',
            'content.documents.view', 'content.chat.access', 'content.comments.create',
        ];
        $memberPermissionIds = Permission::whereIn('key', $memberPermissionKeys)->pluck('id')->toArray();

        Organization::all()->each(function (Organization $org) use ($allPermissionIds, $memberPermissionIds) {
            self::seedRolesForOrg($org, $allPermissionIds, $memberPermissionIds);
        });
    }

    public static function seedRolesForOrg(Organization $org, ?array $allPermissionIds = null, ?array $memberPermissionIds = null): void
    {
        if ($allPermissionIds === null) {
            $allPermissionIds = Permission::pluck('id')->toArray();
        }
        if ($memberPermissionIds === null) {
            $memberPermissionKeys = [
                'org.members.view',
                'board.boards.view', 'board.cards.create', 'board.cards.edit', 'board.cards.move', 'board.chat.access',
                'projects.view', 'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                'time.log', 'time.view_own', 'time.timesheets.submit',
                'financial.budget.view', 'financial.reports.view',
                'content.documents.view', 'content.chat.access', 'content.comments.create',
            ];
            $memberPermissionIds = Permission::whereIn('key', $memberPermissionKeys)->pluck('id')->toArray();
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
