<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->timestamp('start_date')->nullable()->after('due_reminder');
            $table->boolean('is_template')->default(false)->after('is_archived');
            $table->unsignedBigInteger('mirrored_from_card_id')->nullable()->after('is_template');
            $table->foreign('mirrored_from_card_id')->references('id')->on('cards')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign(['mirrored_from_card_id']);
            $table->dropColumn(['start_date', 'is_template', 'mirrored_from_card_id']);
        });
    }
};
