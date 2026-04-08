<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Folders ─────────────────────────────────────────────────
        Schema::create('doc_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('doc_folders')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('color', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'parent_id', 'slug'], 'doc_folders_org_parent_slug_unique');
            $table->index(['organization_id', 'parent_id', 'sort_order']);
        });

        // ── Documents (central table for all 4 types) ───────────────
        Schema::create('doc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('doc_folders')->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['document', 'spreadsheet', 'form', 'presentation'])->default('document');
            $table->string('title', 255)->default('Untitled');
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->longText('body_html')->nullable();   // type=document
            $table->json('body_json')->nullable();        // type=spreadsheet,form,presentation
            $table->json('settings')->nullable();         // type-specific config
            $table->string('status', 20)->default('draft');
            $table->string('sharing_mode', 20)->default('private');
            $table->string('sharing_token', 64)->nullable()->unique();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_template')->default(false);
            $table->unsignedInteger('word_count')->nullable();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug'], 'doc_documents_org_slug_unique');
            $table->index(['organization_id', 'type', 'updated_at']);
            $table->index(['organization_id', 'folder_id']);
            $table->index(['organization_id', 'owner_id']);
        });

        // Full-text search index (MySQL)
        if (config('database.default') === 'mysql') {
            DB::statement('ALTER TABLE doc_documents ADD FULLTEXT doc_documents_fulltext (title, body_html)');
        }

        // ── Shares (per-document permissions) ───────────────────────
        Schema::create('doc_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('doc_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('permission', 20)->default('view'); // view, comment, edit
            $table->foreignId('shared_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'user_id']);
            $table->index(['user_id', 'permission']);
        });

        // ── Stars ───────────────────────────────────────────────────
        Schema::create('doc_stars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('doc_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['document_id', 'user_id']);
            $table->index('user_id');
        });

        // ── Revisions (version history) ─────────────────────────────
        Schema::create('doc_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('doc_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('revision_number')->default(1);
            $table->string('title', 255);
            $table->longText('body_html')->nullable();
            $table->json('body_json')->nullable();
            $table->string('snapshot_type', 20)->default('auto'); // auto, manual, restore
            $table->timestamp('created_at')->useCurrent();

            $table->index(['document_id', 'created_at']);
        });

        // ── Comments ────────────────────────────────────────────────
        Schema::create('doc_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('doc_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('doc_comments')->cascadeOnDelete();
            $table->text('body');
            $table->json('anchor_data')->nullable(); // position context (page, cell ref, slide)
            $table->boolean('is_resolved')->default(false);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['document_id', 'is_resolved']);
            $table->index(['document_id', 'parent_id']);
        });

        // ── Form Responses ───���──────────────────────────────────────
        Schema::create('doc_form_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('doc_documents')->cascadeOnDelete();
            $table->string('respondent_name', 255)->nullable();
            $table->string('respondent_email', 255)->nullable();
            $table->json('data');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at')->useCurrent();

            $table->index(['document_id', 'submitted_at']);
        });

        // ── Attachments ─────────────────────────────────────────────
        Schema::create('doc_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('doc_documents')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('disk', 32)->default('local');
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime', 127);
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'uploaded_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_attachments');
        Schema::dropIfExists('doc_form_responses');
        Schema::dropIfExists('doc_comments');
        Schema::dropIfExists('doc_revisions');
        Schema::dropIfExists('doc_stars');
        Schema::dropIfExists('doc_shares');
        Schema::dropIfExists('doc_documents');
        Schema::dropIfExists('doc_folders');
    }
};
