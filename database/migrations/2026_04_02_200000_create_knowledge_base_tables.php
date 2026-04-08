<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
        });

        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('knowledge_category_id')->constrained('knowledge_categories')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('body_html');
            $table->string('status', 20)->default('draft'); // draft, published
            $table->timestamp('published_at')->nullable();
            $table->boolean('pinned')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'status', 'updated_at']);
            $table->index(['knowledge_category_id', 'status']);
        });

        Schema::create('knowledge_article_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('body_html');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['knowledge_article_id', 'created_at'], 'kb_article_revisions_art_created_idx');
        });

        Schema::create('knowledge_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('knowledge_article_id')->nullable()->constrained('knowledge_articles')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('disk', 32)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 127);
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'uploaded_by'], 'kb_attach_org_user_idx');
        });

        Schema::create('knowledge_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
        });

        Schema::create('knowledge_article_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('knowledge_tag_id')->constrained('knowledge_tags')->cascadeOnDelete();

            $table->unique(['knowledge_article_id', 'knowledge_tag_id'], 'knowledge_article_tag_unique');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE knowledge_articles ADD FULLTEXT knowledge_articles_fulltext (title, body_html)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE knowledge_articles DROP INDEX knowledge_articles_fulltext');
        }

        Schema::dropIfExists('knowledge_article_tag');
        Schema::dropIfExists('knowledge_tags');
        Schema::dropIfExists('knowledge_attachments');
        Schema::dropIfExists('knowledge_article_revisions');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('knowledge_categories');
    }
};
