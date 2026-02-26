<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Engine;

final class EvaluationContext
{
    public function __construct(
        public readonly string $userUuid,
        public readonly string $permission,
        public readonly array $attributes = [],
        public readonly ?string $scope = null,
        public readonly ?string $module = null,
    ) {}

    public function variables(): array
    {
        return [
            ...$this->attributes,
            'user_uuid' => $this->userUuid,
        ];
    }
}
