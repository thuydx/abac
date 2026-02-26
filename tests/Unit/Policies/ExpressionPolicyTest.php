<?php

declare(strict_types=1);

use ThuyDX\ABAC\DSL\Evaluator;
use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\Policies\ExpressionPolicy;
use ThuyDX\ABAC\ValueObjects\Decision;

describe('ExpressionPolicy (ABAC 1.0)', function () {

    beforeEach(function () {
        $this->evaluator = new Evaluator();
        $this->policy    = new ExpressionPolicy($this->evaluator);
    });

    describe('supports()', function () {

        it('returns true for valid DSL expression', function () {
            $expression = [
                'type'       => 'dsl',
                'expression' => 'age >= 18',
            ];

            expect($this->policy->supports($expression))
                ->toBeTrue();
        });

        it('returns false for non-dsl type', function () {
            $expression = [
                'type' => 'structured',
            ];

            expect($this->policy->supports($expression))
                ->toBeFalse();
        });

        it('returns false if expression key missing', function () {
            $expression = [
                'type' => 'dsl',
            ];

            expect($this->policy->supports($expression))
                ->toBeFalse();
        });
    });

    describe('evaluate()', function () {

        it('returns ALLOW when expression evaluates true', function () {
            $expression = [
                'type'       => 'dsl',
                'expression' => 'age >= 18',
            ];

            $context = new EvaluationContext(
                userUuid  : 'user-1',
                permission: 'post.view',
                attributes: ['age' => 20]
            );

            $decision = $this->policy->evaluate($expression, $context);

            expect($decision)->toBe(Decision::ALLOW);
        });

        it('returns ABSTAIN when expression evaluates false', function () {
            $expression = [
                'type'       => 'dsl',
                'expression' => 'age >= 18',
            ];

            $context = new EvaluationContext(
                userUuid  : 'user-1',
                permission: 'post.view',
                attributes: ['age' => 15]
            );

            $decision = $this->policy->evaluate($expression, $context);

            expect($decision)->toBe(Decision::ABSTAIN);
        });

        it('returns DENY when evaluator throws exception', function () {

            $expression = [
                'type'       => 'dsl',
                'expression' => 'age @@ 18', // invalid operator → throws
            ];

            $context = new EvaluationContext(
                userUuid  : 'user-1',
                permission: 'post.view',
                attributes: ['age' => 20]
            );

            $decision = $this->policy->evaluate($expression, $context);

            expect($decision)->toBe(Decision::DENY);
        });
    });
});
