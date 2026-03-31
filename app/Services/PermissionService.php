<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    private array $cache = [];

    public function userCan(User $user, string $permissionKey, ?Organization $org = null): bool
    {
        $org ??= $user->currentOrganization();
        if (!$org) return false;

        // Owner always has full access
        if ($org->owner_id === $user->id) return true;

        // Check system role in organization_members pivot
        $systemRole = $org->members()->where('user_id', $user->id)->value('role');
        if ($systemRole === 'owner') return true;
        if ($systemRole === 'admin') return true;

        // For 'member' system role, check custom role permissions
        return $this->hasPermissionViaRoles($user, $org, $permissionKey);
    }

    public function userPermissions(User $user, ?Organization $org = null): array
    {
        $org ??= $user->currentOrganization();
        if (!$org) return [];

        // Owner/admin get all permissions
        if ($org->owner_id === $user->id) return $this->allPermissionKeys();

        $systemRole = $org->members()->where('user_id', $user->id)->value('role');
        if ($systemRole === 'owner' || $systemRole === 'admin') return $this->allPermissionKeys();

        return $this->loadUserPermissions($user, $org);
    }

    public function hasAnyPermission(User $user, array $keys, ?Organization $org = null): bool
    {
        foreach ($keys as $key) {
            if ($this->userCan($user, $key, $org)) return true;
        }
        return false;
    }

    private function hasPermissionViaRoles(User $user, Organization $org, string $key): bool
    {
        $permissions = $this->loadUserPermissions($user, $org);
        return in_array($key, $permissions);
    }

    private function loadUserPermissions(User $user, Organization $org): array
    {
        $cacheKey = "{$user->id}:{$org->id}";

        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = DB::table('organization_member_roles')
                ->join('role_permissions', 'organization_member_roles.role_id', '=', 'role_permissions.role_id')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('organization_member_roles.organization_id', $org->id)
                ->where('organization_member_roles.user_id', $user->id)
                ->pluck('permissions.key')
                ->unique()
                ->values()
                ->toArray();
        }

        return $this->cache[$cacheKey];
    }

    private function allPermissionKeys(): array
    {
        static $all = null;
        if ($all === null) {
            $all = DB::table('permissions')->pluck('key')->toArray();
        }
        return $all;
    }

    public function userCanForProduct(User $user, string $permissionKey, string $productKey, ?Organization $org = null): bool
    {
        $org ??= $user->currentOrganization();
        if (!$org) return false;
        if ($org->owner_id === $user->id) return true;

        $systemRole = $org->members()->where('user_id', $user->id)->value('role');
        if ($systemRole === 'owner' || $systemRole === 'admin') return true;

        $permissions = $this->loadUserPermissions($user, $org);
        if (!in_array($permissionKey, $permissions)) return false;

        // Verify the permission actually belongs to the requested product
        static $permProductMap = null;
        if ($permProductMap === null) {
            $permProductMap = DB::table('permissions')
                ->leftJoin('products', 'permissions.product_id', '=', 'products.id')
                ->pluck('products.key', 'permissions.key')
                ->toArray();
        }

        $permProduct = $permProductMap[$permissionKey] ?? null;
        return $permProduct === null || $permProduct === $productKey;
    }

    public function userPermissionsForProduct(User $user, string $productKey, ?Organization $org = null): array
    {
        $allPerms = $this->userPermissions($user, $org);

        static $productPermKeys = null;
        if ($productPermKeys === null) {
            $productPermKeys = DB::table('permissions')
                ->leftJoin('products', 'permissions.product_id', '=', 'products.id')
                ->select('permissions.key', 'products.key as product_key')
                ->get()
                ->groupBy('product_key');
        }

        $validKeys = collect();
        // Include global permissions (product_key = null)
        if (isset($productPermKeys[''])) {
            $validKeys = $validKeys->merge($productPermKeys['']->pluck('key'));
        }
        // Include null key group
        foreach ($productPermKeys as $pk => $perms) {
            if ($pk === '' || $pk === $productKey) {
                $validKeys = $validKeys->merge($perms->pluck('key'));
            }
        }

        return array_values(array_intersect($allPerms, $validKeys->toArray()));
    }

    public function allPermissionsGroupedByProduct(): \Illuminate\Support\Collection
    {
        return \App\Models\Permission::with('product')
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => $p->product?->key ?? 'global');
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}
