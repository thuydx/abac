<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Structured\Operators;

use ThuyDX\ABAC\Structured\Constracts\Operators\OperatorInterface;

final class BetweenOperator implements OperatorInterface
{
    public function compare(mixed $actual, mixed $expected): bool
    {
        if (! is_array($expected) || count($expected) !== 2) {
            return false;
        }

        return $actual >= $expected[0]
            && $actual <= $expected[1];
    }
}
