<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_labels', function (Blueprint $table) {
            $table->foreignId('project_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_label_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_task_id', 'project_label_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_labels');
    }
};
