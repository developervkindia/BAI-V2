<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('linked_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->enum('type', ['relates_to', 'blocks', 'blocked_by', 'duplicates']);
            $table->timestamps();

            $table->unique(['task_id', 'linked_task_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_links');
    }
};
