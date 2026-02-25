<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Structured\Constracts\Operators;

interface OperatorInterface
{
    public function compare(mixed $actual, mixed $expected): bool;
}
