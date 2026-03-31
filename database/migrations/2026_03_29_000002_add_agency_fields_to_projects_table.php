<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete()->after('owner_id');
            $table->enum('project_type', ['fixed', 'billing'])->default('fixed')->after('client_id');
            $table->decimal('budget', 12, 2)->nullable()->after('project_type');
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('budget');
            $table->string('srs_url')->nullable()->after('hourly_rate');
            $table->string('design_url')->nullable()->after('srs_url');
            $table->enum('design_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('design_url');
            $table->foreignId('design_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('design_status');
            $table->timestamp('design_approved_at')->nullable()->after('design_approved_by');
            $table->text('design_feedback')->nullable()->after('design_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropConstrainedForeignId('design_approved_by');
            $table->dropColumn([
                'project_type', 'budget', 'hourly_rate',
                'srs_url', 'design_url', 'design_status',
                'design_approved_at', 'design_feedback',
            ]);
        });
    }
};
