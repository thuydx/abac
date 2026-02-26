<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Structured\Operators;

use ThuyDX\ABAC\Structured\Contracts\OperatorInterface;

final class EndsWithOperator implements OperatorInterface
{
    public function compare(mixed $actual, mixed $expected): bool
    {
        return str_ends_with((string) $actual, (string) $expected);
    }
}
