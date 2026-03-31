<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2.1 Billable flag + 2.2 Timer fields on time logs
        Schema::table('project_time_logs', function (Blueprint $table) {
            $table->boolean('is_billable')->default(true)->after('notes');
            $table->timestamp('timer_started_at')->nullable()->after('is_billable');
            $table->timestamp('timer_stopped_at')->nullable()->after('timer_started_at');
            $table->boolean('is_timer_entry')->default(false)->after('timer_stopped_at');
        });

        // 2.4 Timesheet submissions
        Schema::create('timesheet_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id', 'week_start']);
        });

        // 2.6 Cost tracking fields on tasks
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->decimal('cost_rate_override', 8, 2)->nullable()->after('actual_hours');
            $table->decimal('fixed_cost', 10, 2)->nullable()->after('cost_rate_override');
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn(['cost_rate_override', 'fixed_cost']);
        });

        Schema::dropIfExists('timesheet_submissions');

        Schema::table('project_time_logs', function (Blueprint $table) {
            $table->dropColumn(['is_billable', 'timer_started_at', 'timer_stopped_at', 'is_timer_entry']);
        });
    }
};
