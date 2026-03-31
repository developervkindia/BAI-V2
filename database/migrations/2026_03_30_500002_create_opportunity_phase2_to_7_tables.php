<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // PHASE 2: Workflow & Automation
        // ═══════════════════════════════════════════════════════════════

        Schema::create('opp_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('opp_projects')->cascadeOnDelete();
            $t->string('name');
            $t->boolean('is_active')->default(true);
            $t->enum('trigger_type', ['task_added', 'task_completed', 'task_moved', 'field_changed', 'due_date_approaching']);
            $t->json('trigger_config')->nullable();
            $t->enum('action_type', ['assign', 'move_section', 'set_field', 'add_follower', 'mark_complete', 'add_comment']);
            $t->json('action_config')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        Schema::create('opp_approvals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('opp_tasks')->cascadeOnDelete();
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $t->foreignId('requested_by')->constrained('users');
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->text('comment')->nullable();
            $t->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 3: Goals & OKRs
        // ═══════════════════════════════════════════════════════════════

        Schema::create('opp_goals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('opp_goals')->cascadeOnDelete();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->enum('goal_type', ['company', 'team', 'individual'])->default('individual');
            $t->enum('metric_type', ['percentage', 'number', 'currency', 'boolean'])->default('percentage');
            $t->decimal('target_value', 12, 2)->nullable();
            $t->decimal('current_value', 12, 2)->default(0);
            $t->enum('status', ['on_track', 'at_risk', 'off_track', 'achieved', 'dropped'])->default('on_track');
            $t->date('start_date')->nullable();
            $t->date('due_date')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('opp_goal_links', function (Blueprint $t) {
            $t->id();
            $t->foreignId('goal_id')->constrained('opp_goals')->cascadeOnDelete();
            $t->string('linkable_type'); // OppProject or OppTask
            $t->unsignedBigInteger('linkable_id');
            $t->timestamps();
            $t->index(['linkable_type', 'linkable_id']);
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 4: Reporting & Dashboards
        // ═══════════════════════════════════════════════════════════════

        Schema::create('opp_dashboards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->boolean('is_default')->default(false);
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        Schema::create('opp_dashboard_widgets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dashboard_id')->constrained('opp_dashboards')->cascadeOnDelete();
            $t->enum('widget_type', ['chart_bar', 'chart_pie', 'chart_line', 'number', 'task_list', 'progress']);
            $t->string('title');
            $t->json('config')->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->enum('size', ['small', 'medium', 'large'])->default('medium');
            $t->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 5: Portfolio Management
        // ═══════════════════════════════════════════════════════════════

        Schema::create('opp_portfolios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->string('color', 20)->default('#14B8A6');
            $t->timestamps();
        });

        Schema::create('opp_portfolio_projects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_id')->constrained('opp_portfolios')->cascadeOnDelete();
            $t->foreignId('project_id')->constrained('opp_projects')->cascadeOnDelete();
            $t->unsignedInteger('position')->default(0);
            $t->unique(['portfolio_id', 'project_id']);
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 6: Forms & Advanced Features
        // ═══════════════════════════════════════════════════════════════

        Schema::create('opp_forms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('opp_projects')->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->boolean('is_public')->default(false);
            $t->string('slug', 100)->unique();
            $t->json('fields'); // array of field definitions
            $t->json('submit_action')->nullable(); // what happens on submit
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        Schema::create('opp_form_submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('form_id')->constrained('opp_forms')->cascadeOnDelete();
            $t->json('data');
            $t->foreignId('task_id')->nullable()->constrained('opp_tasks')->nullOnDelete();
            $t->string('submitted_by_name')->nullable();
            $t->string('submitted_by_email')->nullable();
            $t->timestamp('created_at');
        });

        Schema::create('opp_saved_searches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->json('filters');
            $t->timestamps();
        });

        Schema::create('opp_favorites', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('favorable_type');
            $t->unsignedBigInteger('favorable_id');
            $t->unsignedInteger('position')->default(0);
            $t->timestamp('created_at');
            $t->index(['favorable_type', 'favorable_id']);
            $t->index(['user_id', 'favorable_type']);
        });

        // Phase 7 uses existing opp_projects.is_template — no new tables needed
    }

    public function down(): void
    {
        Schema::dropIfExists('opp_favorites');
        Schema::dropIfExists('opp_saved_searches');
        Schema::dropIfExists('opp_form_submissions');
        Schema::dropIfExists('opp_forms');
        Schema::dropIfExists('opp_portfolio_projects');
        Schema::dropIfExists('opp_portfolios');
        Schema::dropIfExists('opp_dashboard_widgets');
        Schema::dropIfExists('opp_dashboards');
        Schema::dropIfExists('opp_goal_links');
        Schema::dropIfExists('opp_goals');
        Schema::dropIfExists('opp_approvals');
        Schema::dropIfExists('opp_rules');
    }
};
