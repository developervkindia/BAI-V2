<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('board_id')->nullable()->constrained('boards')->nullOnDelete();
            $table->foreignId('card_id')->nullable()->constrained('cards')->nullOnDelete();
            $table->string('subject_type', 255);
            $table->unsignedBigInteger('subject_id');
            $table->string('action', 100);
            $table->json('data')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['board_id', 'created_at']);
            $table->index(['card_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
