<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('visibility', 20)->default('private')->after('logo_path');
        });

        Schema::create('workspace_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->timestamps();
        });

        Schema::create('workspace_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('workspace_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_group_members');
        Schema::dropIfExists('workspace_groups');
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
