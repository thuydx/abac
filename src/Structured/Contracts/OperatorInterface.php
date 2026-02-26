<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Structured\Contracts;

interface OperatorInterface
{
    public function compare(mixed $actual, mixed $expected): bool;
}
