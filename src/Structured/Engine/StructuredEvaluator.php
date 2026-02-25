<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Structured\Engine;

use ThuyDX\ABAC\Engine\EvaluationContext;

final class StructuredEvaluator
{
    public function __construct(
        protected StructuredOperatorRegistry $registry
    ) {}

    public function evaluate(
        array $rule,
        EvaluationContext $context
    ): bool {
        return $this->evaluateNode($rule, $context);
    }

    private function evaluateNode(
        array $node,
        EvaluationContext $context
    ): bool {

        // Leaf condition
        if (isset($node['field'])) {
            return $this->evaluateCondition($node, $context);
        }

        $operator   = strtolower($node['operator'] ?? 'and');
        $conditions = $node['conditions'] ?? [];

        $results = array_map(
            fn ($child) => $this->evaluateNode($child, $context),
            $conditions
        );

        return match ($operator) {
            'and'   => ! in_array(false, $results, true),
            'or'    => in_array(true, $results, true),
            default => false,
        };
    }

    private function evaluateCondition(
        array $condition,
        EvaluationContext $context
    ): bool {

        $actual   = $context->attributes[$condition['field']] ?? null;
        $expected = $this->resolveDynamicValue(
            $condition['value'] ?? null,
            $context
        );

        $operator = $this->registry->get($condition['operator']);

        return $operator->compare($actual, $expected);
    }

    private function resolveDynamicValue(
        mixed $value,
        EvaluationContext $context
    ): mixed {
        if ($value === 'user_uuid') {
            return $context->userUuid;
        }

        return $value;
    }
}
