<?php

namespace App\Services;

use App\Models\DocAttachment;
use App\Models\DocDocument;
use App\Models\Organization;

class DocsQuotaService
{
    public function __construct(private PlanService $planService) {}

    public function canCreateDocument(Organization $org): bool
    {
        $max = $this->planService->getFeature($org, 'docs', 'max_documents');
        if ($max === null) {
            return true;
        }

        $count = DocDocument::where('organization_id', $org->id)->count();

        return $count < $max;
    }

    public function canCreateFolder(Organization $org): bool
    {
        $max = $this->planService->getFeature($org, 'docs', 'max_folders');
        if ($max === null) {
            return true;
        }

        $count = \App\Models\DocFolder::where('organization_id', $org->id)->count();

        return $count < $max;
    }

    public function storageUsedBytes(Organization $org): int
    {
        return (int) DocAttachment::where('organization_id', $org->id)->sum('size');
    }

    public function canUploadBytes(Organization $org, int $bytes): bool
    {
        $maxMb = $this->planService->getFeature($org, 'docs', 'max_storage_mb');
        if ($maxMb === null) {
            return true;
        }

        $usedBytes = $this->storageUsedBytes($org);

        return ($usedBytes + $bytes) <= ($maxMb * 1024 * 1024);
    }

    public function canCollectFormResponse(Organization $org, DocDocument $form): bool
    {
        $max = $this->planService->getFeature($org, 'docs', 'form_responses_per_form');
        if ($max === null) {
            return true;
        }

        $count = $form->formResponses()->count();

        return $count < $max;
    }
}
