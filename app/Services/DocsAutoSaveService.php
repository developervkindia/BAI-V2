<?php

namespace App\Services;

use App\Models\DocDocument;
use App\Models\DocRevision;
use App\Models\User;

class DocsAutoSaveService
{
    public function __construct(private DocsHtmlSanitizerService $sanitizer) {}

    /**
     * Save document content. Creates a revision if content has changed.
     *
     * @return array{saved: bool, version: int, saved_at: string}
     */
    public function save(DocDocument $document, array $data, User $editor): array
    {
        $changed = false;

        if (isset($data['title']) && $data['title'] !== $document->title) {
            $document->title = $data['title'];
            $changed = true;
        }

        if ($document->isDocument() && isset($data['body_html'])) {
            $sanitized = $this->sanitizer->sanitize($data['body_html']);
            if ($sanitized !== $document->body_html) {
                $document->body_html = $sanitized;
                $changed = true;
            }
        }

        if (! $document->isDocument() && isset($data['body_json'])) {
            $newJson = is_string($data['body_json']) ? json_decode($data['body_json'], true) : $data['body_json'];
            if ($newJson !== $document->body_json) {
                $document->body_json = $newJson;
                $changed = true;
            }
        }

        if (isset($data['settings'])) {
            $newSettings = is_string($data['settings']) ? json_decode($data['settings'], true) : $data['settings'];
            if ($newSettings !== $document->settings) {
                $document->settings = $newSettings;
                $changed = true;
            }
        }

        if (! $changed) {
            return [
                'saved' => false,
                'version' => $document->version,
                'saved_at' => $document->last_edited_at?->toIso8601String() ?? now()->toIso8601String(),
            ];
        }

        $document->version = $document->version + 1;
        $document->last_edited_by = $editor->id;
        $document->last_edited_at = now();
        $document->save();

        return [
            'saved' => true,
            'version' => $document->version,
            'saved_at' => $document->last_edited_at->toIso8601String(),
        ];
    }

    /**
     * Create a revision snapshot for version history.
     */
    public function createRevision(DocDocument $document, User $user, string $snapshotType = 'auto'): DocRevision
    {
        $lastRevision = $document->revisions()->orderByDesc('revision_number')->first();
        $nextNumber = $lastRevision ? $lastRevision->revision_number + 1 : 1;

        return DocRevision::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'revision_number' => $nextNumber,
            'title' => $document->title,
            'body_html' => $document->body_html,
            'body_json' => $document->body_json,
            'snapshot_type' => $snapshotType,
        ]);
    }
}
