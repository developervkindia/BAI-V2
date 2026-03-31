<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Projects
        Schema::create('opp_projects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('color', 20)->default('#14B8A6');
            $t->string('icon', 50)->nullable();
            $t->enum('visibility', ['private', 'public'])->default('public');
            $t->enum('status', ['on_track', 'at_risk', 'off_track', 'completed', 'archived'])->default('on_track');
            $t->date('start_date')->nullable();
            $t->date('due_date')->nullable();
            $t->boolean('is_template')->default(false);
            $t->foreignId('template_id')->nullable()->constrained('opp_projects')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['organization_id', 'slug']);
        });

        // 2. Sections
        Schema::create('opp_sections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('opp_projects')->cascadeOnDelete();
            $t->string('name');
            $t->decimal('position', 10, 3)->default(0);
            $t->timestamps();
        });

        // 3. Tasks
        Schema::create('opp_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('opp_projects')->cascadeOnDelete();
            $t->foreignId('section_id')->nullable()->constrained('opp_sections')->nullOnDelete();
            $t->foreignId('parent_task_id')->nullable()->constrained('opp_tasks')->cascadeOnDelete();
            $t->string('title', 500);
            $t->text('description')->nullable();
            $t->text('description_html')->nullable();
            $t->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('status', ['incomplete', 'complete'])->default('incomplete');
            $t->timestamp('completed_at')->nullable();
            $t->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->date('due_date')->nullable();
            $t->time('due_time')->nullable();
            $t->date('start_date')->nullable();
            $t->boolean('is_milestone')->default(false);
            $t->decimal('position', 10, 3)->default(0);
            $t->unsignedInteger('likes_count')->default(0);
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
            $t->softDeletes();
            $t->index(['project_id', 'section_id', 'position']);
            $t->index(['assignee_id', 'status']);
            $t->index('parent_task_id');
        });

        // 4. Multi-assign
        Schema::create('opp_task_assignees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unique(['task_id', 'user_id']);
        });

        // 5. Followers
        Schema::create('opp_task_followers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unique(['task_id', 'user_id']);
        });

        // 6. Dependencies
        Schema::create('opp_task_dependencies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('depends_on_task_id')->constrained('opp_tasks')->cascadeOnDelete();
            $t->enum('type', ['blocking', 'waiting_on'])->default('blocking');
            $t->unique(['task_id', 'depends_on_task_id']);
        });

        // 7. Tags
        Schema::create('opp_tags', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->string('color', 20)->default('#6B7280');
            $t->timestamps();
            $t->unique(['organization_id', 'name']);
        });

        Schema::create('opp_task_tags', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained('opp_tags')->cascadeOnDelete();
            $t->unique(['task_id', 'tag_id']);
        });

        // 8. Comments
        Schema::create('opp_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->nullable()->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('project_id')->nullable()->constrained('opp_projects')->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('opp_comments')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('body');
            $t->text('body_html')->nullable();
            $t->boolean('is_status_update')->default(false);
            $t->timestamp('edited_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        // 9. Likes
        Schema::create('opp_task_likes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unique(['task_id', 'user_id']);
        });

        // 10. Attachments
        Schema::create('opp_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->nullable()->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('comment_id')->nullable()->constrained('opp_comments')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('filename');
            $t->string('path', 500);
            $t->unsignedInteger('size')->default(0);
            $t->string('mime_type', 100)->nullable();
            $t->timestamps();
        });

        // 11. Project members
        Schema::create('opp_project_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('opp_projects')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->enum('role', ['owner', 'editor', 'commenter', 'viewer'])->default('editor');
            $t->unique(['project_id', 'user_id']);
        });

        // 12. Custom fields
        Schema::create('opp_custom_fields', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->enum('type', ['text', 'number', 'dropdown', 'date', 'checkbox', 'currency', 'person'])->default('text');
            $t->json('options')->nullable();
            $t->boolean('is_required')->default(false);
            $t->timestamps();
        });

        Schema::create('opp_project_custom_fields', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained('opp_projects')->cascadeOnDelete();
            $t->foreignId('custom_field_id')->constrained('opp_custom_fields')->cascadeOnDelete();
            $t->unsignedInteger('position')->default(0);
            $t->unique(['project_id', 'custom_field_id']);
        });

        Schema::create('opp_task_custom_field_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('custom_field_id')->constrained('opp_custom_fields')->cascadeOnDelete();
            $t->text('value')->nullable();
            $t->unique(['task_id', 'custom_field_id']);
        });

        // 13. Activity log
        Schema::create('opp_activity_log', function (Blueprint $t) {
            $t->id();
            $t->foreignId('task_id')->nullable()->constrained('opp_tasks')->cascadeOnDelete();
            $t->foreignId('project_id')->nullable()->constrained('opp_projects')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('action', 100);
            $t->string('field_name', 100)->nullable();
            $t->text('old_value')->nullable();
            $t->text('new_value')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opp_activity_log');
        Schema::dropIfExists('opp_task_custom_field_values');
        Schema::dropIfExists('opp_project_custom_fields');
        Schema::dropIfExists('opp_custom_fields');
        Schema::dropIfExists('opp_project_members');
        Schema::dropIfExists('opp_attachments');
        Schema::dropIfExists('opp_task_likes');
        Schema::dropIfExists('opp_comments');
        Schema::dropIfExists('opp_task_tags');
        Schema::dropIfExists('opp_tags');
        Schema::dropIfExists('opp_task_dependencies');
        Schema::dropIfExists('opp_task_followers');
        Schema::dropIfExists('opp_task_assignees');
        Schema::dropIfExists('opp_tasks');
        Schema::dropIfExists('opp_sections');
        Schema::dropIfExists('opp_projects');
    }
};
