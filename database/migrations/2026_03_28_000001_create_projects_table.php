<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('not_started');
            $table->enum('priority', ['none', 'low', 'medium', 'high', 'critical'])->default('none');
            $table->string('color', 20)->default('#6366f1');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('visibility', ['private', 'organization'])->default('organization');
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
