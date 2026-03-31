<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_saved_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->json('filters')->nullable();
            $table->string('sort_by', 50)->nullable();
            $table->enum('sort_direction', ['asc', 'desc'])->default('asc');
            $table->string('group_by', 50)->nullable();
            $table->string('view_type', 20)->default('list');
            $table->boolean('is_shared')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_saved_views');
    }
};
