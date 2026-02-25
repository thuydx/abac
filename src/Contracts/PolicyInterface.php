<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Contracts;

use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\ValueObjects\Decision;

interface PolicyInterface
{
    public function supports(array $expression): bool;

    public function evaluate(
        array $expression,
        EvaluationContext $context
    ): Decision;
}
