<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Contracts;

use ThuyDX\ABAC\Engine\EvaluationContext;

interface AbacEngineInterface
{
    public function evaluate(EvaluationContext $context): bool;
}
