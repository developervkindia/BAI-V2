<?php

namespace App\Policies;

use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\PermissionService;

class KnowledgeArticlePolicy
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

    public function view(User $user, KnowledgeArticle $article): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        if (! $this->permissions->userCan($user, 'knowledge.view')) {
            return false;
        }

        if ($this->permissions->userCan($user, 'knowledge.moderate')) {
            return true;
        }

        if ($article->isPublished()) {
            return true;
        }

        return $this->permissions->userCan($user, 'knowledge.contribute')
            && $article->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->permissions->userCan($user, 'knowledge.contribute')
            || $this->permissions->userCan($user, 'knowledge.moderate');
    }

    public function update(User $user, KnowledgeArticle $article): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        if ($this->permissions->userCan($user, 'knowledge.moderate')) {
            return true;
        }

        return $this->permissions->userCan($user, 'knowledge.contribute')
            && $article->author_id === $user->id;
    }

    public function delete(User $user, KnowledgeArticle $article): bool
    {
        return $this->update($user, $article);
    }

    public function restore(User $user, KnowledgeArticle $article): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->permissions->userCan($user, 'knowledge.moderate');
    }

    public function forceDelete(User $user, KnowledgeArticle $article): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->permissions->userCan($user, 'knowledge.moderate');
    }
}
