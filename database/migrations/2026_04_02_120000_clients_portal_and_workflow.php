<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('stage', 32)->default('prospect')->after('notes');
            $table->timestamp('requirements_approved_at')->nullable()->after('stage');
            $table->foreignId('hired_project_id')->nullable()->after('requirements_approved_at')
                ->constrained('projects')->nullOnDelete();
        });

        Schema::create('client_portal_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->unique('email');
            $table->index('client_id');
        });

        Schema::create('client_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('disk', 32)->default('local');
            $table->string('visibility', 16)->default('internal'); // internal | portal
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_documents');
        Schema::dropIfExists('client_portal_users');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hired_project_id');
            $table->dropColumn(['stage', 'requirements_approved_at']);
        });
    }
};
