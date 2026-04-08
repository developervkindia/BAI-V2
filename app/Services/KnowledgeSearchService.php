<?php

namespace App\Services;

use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KnowledgeSearchService
{
    public function search(User $user, int $organizationId, string $query, bool $includeDraftsForContributor): Collection
    {
        $q = trim($query);
        if ($q === '') {
            return collect();
        }

        $driver = DB::getDriverName();

        $base = KnowledgeArticle::query()
            ->where('organization_id', $organizationId)
            ->with(['category', 'author']);

        $this->visibilityScope($base, $user, $organizationId, $includeDraftsForContributor);

        if ($driver === 'mysql') {
            $base->whereRaw(
                'MATCH(title, body_html) AGAINST(? IN NATURAL LANGUAGE MODE)',
                [$q]
            );
        } else {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $base->where(function (Builder $b) use ($like) {
                $b->where('title', 'like', $like)
                    ->orWhere('body_html', 'like', $like);
            });
        }

        return $base->orderByDesc('updated_at')->limit(50)->get();
    }

    private function visibilityScope(Builder $base, User $user, int $organizationId, bool $includeDraftsForContributor): void
    {
        $permissionService = app(PermissionService::class);
        $canModerate = $permissionService->userCan($user, 'knowledge.moderate')
            || $user->is_super_admin;

        if ($canModerate) {
            return;
        }

        $canContribute = $permissionService->userCan($user, 'knowledge.contribute');

        $base->where(function (Builder $b) use ($user, $includeDraftsForContributor, $canContribute) {
            $b->where('status', 'published');
            if ($includeDraftsForContributor && $canContribute) {
                $b->orWhere(function (Builder $inner) use ($user) {
                    $inner->where('status', 'draft')->where('author_id', $user->id);
                });
            }
        });
    }
}
