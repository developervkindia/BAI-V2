<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->string('color', 20)->default('#94A3B8');
            $table->decimal('position', 10, 3)->default(0);
            $table->boolean('is_completed_state')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'slug']);
            $table->index(['project_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_statuses');
    }
};
