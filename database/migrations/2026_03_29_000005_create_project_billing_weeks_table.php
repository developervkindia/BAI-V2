<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_billing_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->decimal('total_actual_hours', 8, 2)->default(0);
            $table->decimal('total_billable_hours', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('invoice_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_billing_weeks');
    }
};
