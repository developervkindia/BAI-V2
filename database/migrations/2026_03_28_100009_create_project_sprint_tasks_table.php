<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_sprint_tasks', function (Blueprint $table) {
            $table->foreignId('sprint_id')->constrained('project_sprints')->cascadeOnDelete();
            $table->foreignId('project_task_id')->constrained()->cascadeOnDelete();
            $table->primary(['sprint_id', 'project_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_sprint_tasks');
    }
};
