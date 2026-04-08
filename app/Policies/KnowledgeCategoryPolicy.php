<?php

namespace App\Policies;

use App\Models\KnowledgeCategory;
use App\Models\User;
use App\Services\PermissionService;

class KnowledgeCategoryPolicy
{
    public function __construct(
        protected PermissionService $permissions,
    ) {}

    public function viewAny(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->permissions->userCan($user, 'knowledge.view');
    }

    public function view(User $user, KnowledgeCategory $category): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->permissions->userCan($user, 'knowledge.moderate');
    }

    public function update(User $user, KnowledgeCategory $category): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, KnowledgeCategory $category): bool
    {
        return $this->create($user);
    }
}
