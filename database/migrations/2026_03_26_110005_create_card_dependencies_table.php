<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('depends_on_card_id');
            $table->foreign('depends_on_card_id')->references('id')->on('cards')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->unique(['card_id', 'depends_on_card_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_dependencies');
    }
};
