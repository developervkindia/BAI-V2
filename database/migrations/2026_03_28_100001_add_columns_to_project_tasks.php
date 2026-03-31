<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->enum('issue_type', ['task', 'bug', 'story', 'epic'])->default('task')->after('title');
            $table->unsignedSmallInteger('story_points')->nullable()->after('issue_type');
            $table->decimal('actual_hours', 5, 2)->nullable()->after('estimated_hours');
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn(['issue_type', 'story_points', 'actual_hours']);
        });
    }
};
