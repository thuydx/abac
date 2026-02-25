<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Contracts;

interface ConstraintRepositoryInterface
{
    public function forUserAndPermission(
        string $userUuid,
        string $permissionSlug,
        ?string $scope = null,
        ?string $module = null,
    ): array;
}
