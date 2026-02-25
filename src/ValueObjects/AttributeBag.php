<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\ValueObjects;

final class AttributeBag
{
    public function __construct(
        protected array $attributes
    ) {}

    public function get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function all(): array
    {
        return $this->attributes;
    }
}
