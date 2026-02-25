<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Policies;

use ThuyDX\ABAC\Contracts\PolicyInterface;
use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\Structured\Engine\StructuredEvaluator;
use ThuyDX\ABAC\ValueObjects\Decision;

final class StructuredPolicy implements PolicyInterface
{
    public function __construct(
        protected StructuredEvaluator $evaluator
    ) {}

    public function supports(array $expression): bool
    {
        return ($expression['type'] ?? null) === 'structured';
    }

    public function evaluate(
        array $expression,
        EvaluationContext $context
    ): Decision {

        $passed = $this->evaluator->evaluate(
            $expression['rules'] ?? [],
            $context
        );

        if (! $passed) {
            return Decision::ABSTAIN;
        }

        return Decision::from(
            $expression['decision'] ?? 'allow'
        );
    }
}
