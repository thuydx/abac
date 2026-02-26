<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Engine;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class EvaluationContextFactory
{
    public static function fromAuth(
        string $permission,
        array $attributes = [],
        ?string $scope = null,
        ?string $module = null,
    ): EvaluationContext {
        $user = auth()->user();

        if (! $user) {
            throw new HttpException(
                401,
                'Unauthenticated.'
            );
        }

        return new EvaluationContext(
            userUuid  : $user->uuid,
            permission: $permission,
            attributes: $attributes,
            scope     : $scope,
            module    : $module,
        );
    }
}
