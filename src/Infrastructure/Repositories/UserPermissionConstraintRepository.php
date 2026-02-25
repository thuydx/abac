<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Infrastructure\Repositories;

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
            ->pluck('expression')
            ->all();
    }

    public function forUser(string $userUuid): array
    {
        return UserPermissionConstraint::query()
            ->where('user_uuid', $userUuid)
            ->pluck('expression')
            ->all();
    }
}
