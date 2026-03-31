<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false)->after('locale');
                $table->index('is_super_admin');
            });
        }

        if (!Schema::hasColumn('organizations', 'is_active')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('owner_id');
                $table->timestamp('deactivated_at')->nullable()->after('is_active');
                $table->index('is_active');
            });
        }

        if (!Schema::hasColumn('permissions', 'product_id')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('group')
                      ->constrained('products')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('super_admin_audit_logs')) {
            Schema::create('super_admin_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('action', 100);
                $table->string('target_type', 100)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at');

                $table->index(['target_type', 'target_id']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admin_audit_logs');
        if (Schema::hasColumn('permissions', 'product_id')) {
            Schema::table('permissions', fn($t) => $t->dropConstrainedForeignId('product_id'));
        }
        if (Schema::hasColumn('organizations', 'is_active')) {
            Schema::table('organizations', fn($t) => $t->dropColumn(['is_active', 'deactivated_at']));
        }
        if (Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', fn($t) => $t->dropColumn('is_super_admin'));
        }
    }
};
