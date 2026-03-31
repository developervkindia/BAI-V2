<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Board email address for email-to-board feature
        Schema::table('boards', function (Blueprint $table) {
            $table->string('email_address', 255)->nullable()->unique()->after('visibility');
        });

        // User notification preferences
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn('email_address');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
    }
};
