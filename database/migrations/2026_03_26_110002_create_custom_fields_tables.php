<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->enum('type', ['text', 'dropdown', 'date', 'checkbox', 'number']);
            $table->json('options')->nullable();
            $table->decimal('position', 20, 10)->default(0);
            $table->timestamps();
        });

        Schema::create('card_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['card_id', 'custom_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
