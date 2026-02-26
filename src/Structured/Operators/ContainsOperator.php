<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Structured\Operators;

use ThuyDX\ABAC\Structured\Contracts\OperatorInterface;

final class ContainsOperator implements OperatorInterface
{
    public function compare(mixed $actual, mixed $expected): bool
    {
        return str_contains((string) $actual, (string) $expected);
    }
}
