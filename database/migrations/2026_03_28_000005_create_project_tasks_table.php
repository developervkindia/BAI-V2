<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_list_id')->constrained('task_lists')->cascadeOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('milestones')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'in_progress', 'completed', 'deferred'])->default('open');
            $table->enum('priority', ['none', 'low', 'medium', 'high', 'critical'])->default('none');
            $table->decimal('position', 10, 3)->default(0);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index(['task_list_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
