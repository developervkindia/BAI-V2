<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_billing_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_week_id')->constrained('project_billing_weeks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->decimal('billable_hours', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['billing_week_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_billing_entries');
    }
};
