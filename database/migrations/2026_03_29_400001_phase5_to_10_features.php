<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Helper: only create table if it doesn't exist
        $createIfNotExists = function (string $table, \Closure $callback) {
            if (!Schema::hasTable($table)) {
                Schema::create($table, $callback);
            }
        };

        // Helper: only add column if it doesn't exist
        $addColumnIfNotExists = function (string $table, string $column, \Closure $callback) {
            if (!Schema::hasColumn($table, $column)) {
                Schema::table($table, $callback);
            }
        };

        // Phase 5.1 — Threaded comments
        $addColumnIfNotExists('project_comments', 'parent_id', function (Blueprint $t) {
            $t->foreignId('parent_id')->nullable()->after('id')->constrained('project_comments')->cascadeOnDelete();
        });
        $addColumnIfNotExists('project_comments', 'edited_at', function (Blueprint $t) {
            $t->timestamp('edited_at')->nullable()->after('content');
        });

        // Phase 5.2 — Task checklists
        $createIfNotExists('project_task_checklists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $t->string('name');
            $t->decimal('position', 10, 3)->default(0);
            $t->timestamps();
        });
        $createIfNotExists('project_task_checklist_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_task_checklist_id')->constrained('project_task_checklists')->cascadeOnDelete();
            $t->string('content', 500);
            $t->boolean('is_checked')->default(false);
            $t->decimal('position', 10, 3)->default(0);
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->date('due_date')->nullable();
            $t->timestamps();
        });

        // Phase 5.4 — Project chat
        $createIfNotExists('project_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('body');
            $t->timestamps();
            $t->index(['project_id', 'created_at']);
        });

        // Phase 5.5 — Document hub
        $createIfNotExists('project_folders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('project_folders')->cascadeOnDelete();
            $t->string('name');
            $t->integer('position')->default(0);
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });
        $addColumnIfNotExists('attachments', 'project_folder_id', function (Blueprint $t) {
            $t->foreignId('project_folder_id')->nullable()->after('attachable_id')->constrained('project_folders')->nullOnDelete();
        });

        // Phase 6.2 — Recurring tasks
        $createIfNotExists('recurring_task_patterns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('task_list_id')->constrained('task_lists');
            $t->string('title');
            $t->text('description')->nullable();
            $t->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('priority', ['none', 'low', 'medium', 'high', 'critical'])->default('none');
            $t->enum('issue_type', ['task', 'bug', 'story', 'epic'])->default('task');
            $t->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly'])->default('weekly');
            $t->unsignedTinyInteger('day_of_week')->nullable();
            $t->unsignedTinyInteger('day_of_month')->nullable();
            $t->date('next_run_date');
            $t->date('last_run_date')->nullable();
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        // Phase 6.3 — Task reminders
        $createIfNotExists('project_task_reminders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamp('remind_at');
            $t->enum('type', ['before_due', 'custom'])->default('before_due');
            $t->boolean('is_sent')->default(false);
            $t->timestamps();
        });

        // Phase 6.4 — Dependency types
        $addColumnIfNotExists('project_task_links', 'lag_days', function (Blueprint $t) {
            $t->integer('lag_days')->default(0)->after('type');
        });

        // Phase 7.1 — Client portal
        $addColumnIfNotExists('clients', 'password', function (Blueprint $t) {
            $t->string('password')->nullable()->after('email');
            $t->string('portal_token', 100)->nullable()->unique()->after('password');
            $t->boolean('portal_enabled')->default(false)->after('portal_token');
            $t->timestamp('last_login_at')->nullable()->after('portal_enabled');
        });
        $createIfNotExists('client_portal_settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $t->boolean('is_portal_enabled')->default(false);
            $t->boolean('show_tasks')->default(true);
            $t->boolean('show_milestones')->default(true);
            $t->boolean('show_files')->default(true);
            $t->boolean('show_updates')->default(true);
            $t->boolean('show_billing')->default(false);
            $t->timestamps();
        });

        // Phase 7.2 — Client feedback
        $createIfNotExists('client_feedback', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('client_id')->constrained()->cascadeOnDelete();
            $t->string('feedbackable_type');
            $t->unsignedBigInteger('feedbackable_id');
            $t->unsignedTinyInteger('rating')->nullable();
            $t->text('comment');
            $t->enum('status', ['pending', 'acknowledged', 'addressed'])->default('pending');
            $t->timestamps();
            $t->index(['feedbackable_type', 'feedbackable_id']);
        });

        // Phase 8.2 — Project templates
        $createIfNotExists('project_templates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->json('structure');
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        // Phase 8.3 — Recycle bin
        $addColumnIfNotExists('task_lists', 'deleted_at', function (Blueprint $t) {
            $t->softDeletes();
        });
        $addColumnIfNotExists('milestones', 'deleted_at', function (Blueprint $t) {
            $t->softDeletes();
        });

        // Phase 8.4 — Business calendar
        $createIfNotExists('business_calendars', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->boolean('is_default')->default(false);
            $t->json('working_days')->nullable();
            $t->timestamps();
        });
        $createIfNotExists('holidays', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_calendar_id')->constrained('business_calendars')->cascadeOnDelete();
            $t->string('name');
            $t->date('date');
            $t->boolean('is_recurring')->default(false);
            $t->timestamps();
            $t->index(['business_calendar_id', 'date']);
        });

        // Phase 8.5 — Multi-language
        $addColumnIfNotExists('users', 'locale', function (Blueprint $t) {
            $t->string('locale', 10)->default('en')->after('email');
        });

        // Phase 10.1 — Webhooks
        $createIfNotExists('webhooks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('url', 500);
            $t->string('secret')->nullable();
            $t->json('events');
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_triggered_at')->nullable();
            $t->integer('last_response_code')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });
        $createIfNotExists('webhook_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $t->string('event');
            $t->json('payload');
            $t->integer('response_code')->nullable();
            $t->text('response_body')->nullable();
            $t->timestamp('created_at');
        });

        // Phase 10.2 — Slack
        $createIfNotExists('slack_integrations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('channel_name');
            $t->string('webhook_url', 500);
            $t->json('events');
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        // Phase 10.3 — GitHub
        $createIfNotExists('github_integrations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('repository_url', 500);
            $t->text('access_token');
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });
        $createIfNotExists('github_task_links', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $t->foreignId('github_integration_id')->constrained('github_integrations')->cascadeOnDelete();
            $t->enum('type', ['commit', 'pull_request', 'branch', 'issue']);
            $t->string('github_url', 500);
            $t->string('github_id')->nullable();
            $t->string('title')->nullable();
            $t->string('status')->nullable();
            $t->string('author')->nullable();
            $t->timestamp('created_at_github')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_task_links');
        Schema::dropIfExists('github_integrations');
        Schema::dropIfExists('slack_integrations');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
        if (Schema::hasColumn('users', 'locale')) Schema::table('users', fn($t) => $t->dropColumn('locale'));
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('business_calendars');
        if (Schema::hasColumn('milestones', 'deleted_at')) Schema::table('milestones', fn($t) => $t->dropSoftDeletes());
        if (Schema::hasColumn('task_lists', 'deleted_at')) Schema::table('task_lists', fn($t) => $t->dropSoftDeletes());
        Schema::dropIfExists('project_templates');
        Schema::dropIfExists('client_feedback');
        Schema::dropIfExists('client_portal_settings');
        if (Schema::hasColumn('clients', 'password')) Schema::table('clients', fn($t) => $t->dropColumn(['password', 'portal_token', 'portal_enabled', 'last_login_at']));
        if (Schema::hasColumn('project_task_links', 'lag_days')) Schema::table('project_task_links', fn($t) => $t->dropColumn('lag_days'));
        Schema::dropIfExists('project_task_reminders');
        Schema::dropIfExists('recurring_task_patterns');
        if (Schema::hasColumn('attachments', 'project_folder_id')) Schema::table('attachments', fn($t) => $t->dropConstrainedForeignId('project_folder_id'));
        Schema::dropIfExists('project_folders');
        Schema::dropIfExists('project_messages');
        Schema::dropIfExists('project_task_checklist_items');
        Schema::dropIfExists('project_task_checklists');
    }
};
