<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_members', function (Blueprint $table) {
            $table->foreignId('project_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_members');
    }
};
