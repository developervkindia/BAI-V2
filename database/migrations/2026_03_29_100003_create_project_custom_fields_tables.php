<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['text', 'number', 'date', 'dropdown', 'checkbox', 'url'])->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->decimal('position', 10, 3)->default(0);
            $table->timestamps();

            $table->index('project_id');
        });

        Schema::create('project_task_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('custom_field_id')->constrained('project_custom_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['project_task_id', 'custom_field_id'], 'task_field_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_custom_field_values');
        Schema::dropIfExists('project_custom_fields');
    }
};
