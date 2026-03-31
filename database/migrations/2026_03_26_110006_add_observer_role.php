<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate columns since ALTER ENUM isn't supported
        // We'll use string type instead of enum for flexibility
        Schema::table('board_members', function (Blueprint $table) {
            $table->string('role_new', 20)->default('normal')->after('role');
        });
        DB::table('board_members')->update(['role_new' => DB::raw('role')]);
        Schema::table('board_members', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        Schema::table('board_members', function (Blueprint $table) {
            $table->renameColumn('role_new', 'role');
        });

        Schema::table('workspace_members', function (Blueprint $table) {
            $table->string('role_new', 20)->default('normal')->after('role');
        });
        DB::table('workspace_members')->update(['role_new' => DB::raw('role')]);
        Schema::table('workspace_members', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        Schema::table('workspace_members', function (Blueprint $table) {
            $table->renameColumn('role_new', 'role');
        });

        // Add is_guest flag to board_members
        Schema::table('board_members', function (Blueprint $table) {
            $table->boolean('is_guest')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('board_members', function (Blueprint $table) {
            $table->dropColumn('is_guest');
        });
    }
};
