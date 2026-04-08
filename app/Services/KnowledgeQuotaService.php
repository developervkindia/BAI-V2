<?php

namespace App\Services;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeAttachment;
use App\Models\KnowledgeCategory;
use App\Models\Organization;

class KnowledgeQuotaService
{
    public function __construct(
        protected PlanService $planService,
    ) {}

    public function canCreateCategory(Organization $org): bool
    {
        $limit = $this->planService->getFeature($org, 'knowledge_base', 'max_categories');
        if ($limit === null) {
            return true;
        }
        $count = KnowledgeCategory::where('organization_id', $org->id)->count();

        return $count < (int) $limit;
    }

    public function canCreateArticle(Organization $org): bool
    {
        $limit = $this->planService->getFeature($org, 'knowledge_base', 'max_articles');
        if ($limit === null) {
            return true;
        }
        $count = KnowledgeArticle::where('organization_id', $org->id)->count();

        return $count < (int) $limit;
    }

    public function storageUsedBytes(Organization $org): int
    {
        return (int) KnowledgeAttachment::where('organization_id', $org->id)->sum('size');
    }

    public function canUploadBytes(Organization $org, int $additionalBytes): bool
    {
        $limitMb = $this->planService->getFeature($org, 'knowledge_base', 'max_storage_mb');
        if ($limitMb === null) {
            return true;
        }
        $maxBytes = (int) $limitMb * 1024 * 1024;
        $used = $this->storageUsedBytes($org);

        return ($used + $additionalBytes) <= $maxBytes;
    }

    public function attachmentsEnabled(Organization $org): bool
    {
        return (bool) $this->planService->getFeature($org, 'knowledge_base', 'attachments');
    }
}
