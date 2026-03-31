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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_list_id')->constrained('board_lists')->cascadeOnDelete();
            $table->foreignId('board_id')->constrained('boards')->cascadeOnDelete();
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->decimal('position', 20, 10);
            $table->string('cover_image_path', 500)->nullable();
            $table->string('cover_color', 7)->nullable();
            $table->timestamp('due_date')->nullable();
            $table->boolean('due_date_complete')->default(false);
            $table->string('due_reminder', 20)->nullable();
            $table->boolean('is_archived')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['board_list_id', 'position']);
            $table->index(['board_id', 'is_archived']);
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
