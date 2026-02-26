<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Engine;

use ThuyDX\ABAC\ValueObjects\Decision;

final class DecisionTrace
{
    public array $steps = [];

    public function add(
        array $expression,
        string $policy,
        Decision $decision
    ): void {
        $this->steps[] = [
            'expression' => $expression,
            'policy' => $policy,
            'decision' => $decision->value,
        ];
    }

    public function all(): array
    {
        return $this->steps;
    }
}
