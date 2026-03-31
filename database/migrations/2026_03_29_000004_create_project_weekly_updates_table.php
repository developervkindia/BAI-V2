<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_weekly_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->enum('period_type', ['weekly', 'biweekly'])->default('weekly');
            $table->date('week_start');
            $table->date('week_end');
            $table->text('summary');
            $table->text('next_steps')->nullable();
            $table->text('blockers')->nullable();
            $table->foreignId('qa_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('qa_approved_at')->nullable();
            $table->timestamp('shared_with_client_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_weekly_updates');
    }
};
