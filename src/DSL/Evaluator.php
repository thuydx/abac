<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\DSL;

use RuntimeException;

final class Evaluator
{
    private array $astCache = [];

    public function evaluate(
        string $expression,
        array $variables
    ): bool {

        if (! isset($this->astCache[$expression])) {
            $tokens = (new Tokenizer($expression))->tokenize();
            $ast    = (new ExpressionParser($tokens))->parse();
            $this->astCache[$expression] = $ast;
        }

        return (bool) $this->evaluateNode(
            $this->astCache[$expression],
            $variables
        );
    }

    private function evaluateNode(
        mixed $node,
        array $variables
    ): mixed {

        if (! is_array($node)) {
            return $node;
        }

        return match ($node[0]) {
            'literal'  => $variables[$node[1]] ?? $node[1],
            'array'    => array_map(
                fn ($v) => $variables[$v] ?? $v,
                $node[1]
            ),
            'compare'  => Operators::compare(
                $this->evaluateNode($node[2], $variables),
                $node[1],
                $this->evaluateNode($node[3], $variables)
            ),
            'and'      => $this->evaluateNode($node[1], $variables)
                && $this->evaluateNode($node[2], $variables),
            'or'       => $this->evaluateNode($node[1], $variables)
                || $this->evaluateNode($node[2], $variables),
            'not'      => ! $this->evaluateNode($node[1], $variables),
            'function' => Operators::callFunction(
                $node[1],
                array_map(
                    fn ($arg) => $this->evaluateNode($arg, $variables),
                    $node[2]
                )
            ),
            default    => throw new RuntimeException('Invalid AST node')
        };
    }
}

/**
 * Example usage:
 * $evaluator = new Evaluator();
 *
 * $result = $evaluator->evaluate(
 * "!startsWith(category, 'admin') && owner_uuid == user_uuid",
 * [
 * 'owner_uuid' => 'abc',
 * 'user_uuid'  => 'abc',
 * 'category'   => 'marketing'
 * ]
 * );
 *
 * $evaluator->evaluate(
 * "contains(category, 'market')",
 * ['category' => 'marketing']
 * );
 * // true
 *
 * $evaluator->evaluate(
 * "endsWith(email, '@company.com')",
 * ['email' => 'admin@company.com']
 * );
 * // true
 *
 * $evaluator->evaluate(
 * "created_at >= '2025-01-01'",
 * ['created_at' => '2025-02-01']
 * );
 * // true
 *
 * $evaluator->evaluate(
 * "now() < expires_at",
 * ['expires_at' => '2026-01-01']
 * );
 */
