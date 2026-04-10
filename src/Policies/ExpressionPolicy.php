<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Policies;

use Throwable;
use ThuyDX\ABAC\Contracts\PolicyInterface;
use ThuyDX\ABAC\DSL\Evaluator;
use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\ValueObjects\Decision;

final class ExpressionPolicy implements PolicyInterface
{
    public function __construct(
        protected Evaluator $evaluator
    ) {}

    public function supports(array $expression): bool
    {
        return ($expression['type'] ?? null) === 'dsl'
            && isset($expression['expression'])
            && is_string($expression['expression']);
    }

    public function evaluate(
        array $expression,
        EvaluationContext $context
    ): Decision {

        try {
            $result = $this->evaluator->evaluate(
                $expression['expression'],
                $context->variables()
            );

            return Decision::fromBoolean($result);

        } catch (Throwable) {
            return Decision::DENY; // fail-safe
        }
    }
}
