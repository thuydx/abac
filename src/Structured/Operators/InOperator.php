<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Structured\Operators;

use ThuyDX\ABAC\Structured\Contracts\OperatorInterface;

final class InOperator implements OperatorInterface
{
    public function compare(mixed $actual, mixed $expected): bool
    {
        return in_array($actual, (array) $expected, true);
    }
}
