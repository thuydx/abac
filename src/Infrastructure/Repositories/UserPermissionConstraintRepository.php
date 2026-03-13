<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Infrastructure\Repositories;

use Illuminate\Support\Facades\Cache;
use ThuyDX\ABAC\Contracts\ConstraintRepositoryInterface;
use ThuyDX\ABAC\Infrastructure\Models\UserPermissionConstraint;

final class UserPermissionConstraintRepository implements ConstraintRepositoryInterface
{
    public function forUserAndPermission(
        string $userUuid,
        string $permissionSlug,
        ?string $scope = null,
        ?string $module = null,
    ): array {

        if (! config('abac.cache.enabled')) {
            return $this->queryConstraints(
                $userUuid,
                $permissionSlug,
                $scope,
                $module
            );
        }

        $key = $this->cacheKey(
            $userUuid,
            $permissionSlug,
            $scope,
            $module
        );

        return Cache::store(config('abac.cache.store'))
            ->tags([
                'abac',
                'user:'.$userUuid,
                'permission:'.$permissionSlug,
            ])
            ->remember(
                $key,
                config('abac.cache.ttl'),
                fn () => $this->queryConstraints(
                    $userUuid,
                    $permissionSlug,
                    $scope,
                    $module
                )
            );
    }

    public function forUser(string $userUuid): array
    {
        return UserPermissionConstraint::query()
            ->where('user_uuid', $userUuid)
            ->pluck('expression')
            ->all();
    }

    private function cacheKey(
        string $userUuid,
        string $permission,
        ?string $scope,
        ?string $module
    ): string {

        return sprintf(
            'abac:constraints:%s:%s:%s:%s',
            $userUuid,
            $permission,
            $scope ?? 'null',
            $module ?? 'null'
        );
    }

    private function queryConstraints(
        string $userUuid,
        string $permissionSlug,
        ?string $scope,
        ?string $module
    ): array {

        $query = UserPermissionConstraint::query()
            ->where('user_uuid', $userUuid)
            ->where('permission', $permissionSlug);

        if ($scope !== null) {
            $query->where('scope', $scope);
        }

        if ($module !== null) {
            $query->where('module', $module);
        }

        return $query
            ->orderByDesc('priority')
            ->get(['expression', 'priority'])
            ->map(fn ($row) => [
                ...$row->expression,
                'priority' => $row->priority,
            ])
            ->all();
    }
}
