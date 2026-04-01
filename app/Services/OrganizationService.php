<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;

class OrganizationService
{
    public function __construct(
        protected ProductAccessService $productAccess,
        protected OrgMemberOnboardingService $onboarding,
    ) {}

    public function createForUser(User $user, array $data): Organization
    {
        $org = Organization::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id'    => $user->id,
        ]);

        // Attach owner as a member with 'owner' role
        $org->members()->attach($user->id, ['role' => 'owner']);

        // Auto-provision free product subscriptions
        $this->productAccess->provisionFreeSmartBoard($org);

        // Cross-product onboarding for the owner
        $this->onboarding->provisionMember($org, $user, 'owner');

        return $org;
    }
}
