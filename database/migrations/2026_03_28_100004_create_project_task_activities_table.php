<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // field_changed, created, commented, etc.
            $table->string('field_name', 50)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // no updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_activities');
    }
};
