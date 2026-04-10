<?php

declare(strict_types=1);

use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\Policies\StructuredPolicy;
use ThuyDX\ABAC\Structured\Engine\StructuredEvaluator;
use ThuyDX\ABAC\Structured\Engine\StructuredOperatorRegistry;
use ThuyDX\ABAC\ValueObjects\Decision;

describe('StructuredPolicy (ABAC 1.0)', function () {

    beforeEach(function () {
        $registry = new StructuredOperatorRegistry();
        $evaluator = new StructuredEvaluator($registry);
        $this->policy = new StructuredPolicy($evaluator);
    });

    describe('supports()', function () {

        it('returns true for structured type', function () {
            expect(
                $this->policy->supports(['type' => 'structured'])
            )->toBeTrue();
        });

        it('returns false for other types', function () {
            expect(
                $this->policy->supports(['type' => 'dsl'])
            )->toBeFalse();
        });

    });

    describe('evaluate()', function () {

        it('returns ALLOW when rule passes and decision is allow', function () {

            $expression = [
                'type' => 'structured',
                'decision' => 'allow',
                'rules' => [
                    'field' => 'age',
                    'operator' => '>=',
                    'value' => 18,
                ],
            ];

            $context = new EvaluationContext(
                userUuid: 'user-1',
                permission: 'post.view',
                attributes: ['age' => 20]
            );

            $decision = $this->policy->evaluate($expression, $context);

            expect($decision)->toBe(Decision::ALLOW);
        });

        it('returns DENY when rule passes and decision is deny', function () {

            $expression = [
                'type' => 'structured',
                'decision' => 'deny',
                'rules' => [
                    'field' => 'age',
                    'operator' => '>=',
                    'value' => 18,
                ],
            ];

            $context = new EvaluationContext(
                userUuid: 'user-1',
                permission: 'post.view',
                attributes: ['age' => 20]
            );

            $decision = $this->policy->evaluate($expression, $context);

            expect($decision)->toBe(Decision::DENY);
        });

        it('returns ABSTAIN when rule fails', function () {

            $expression = [
                'type' => 'structured',
                'decision' => 'allow',
                'rules' => [
                    'field' => 'age',
                    'operator' => '>=',
                    'value' => 18,
                ],
            ];

            $context = new EvaluationContext(
                userUuid: 'user-1',
                permission: 'post.view',
                attributes: ['age' => 15]
            );

            $decision = $this->policy->evaluate($expression, $context);

            expect($decision)->toBe(Decision::ABSTAIN);
        });

        it('defaults to allow when decision missing and rule passes', function () {

            $expression = [
                'type' => 'structured',
                'rules' => [
                    'field' => 'age',
                    'operator' => '>=',
                    'value' => 18,
                ],
            ];

            $context = new EvaluationContext(
                userUuid: 'user-1',
                permission: 'post.view',
                attributes: ['age' => 20]
            );

            $decision = $this->policy->evaluate($expression, $context);

            expect($decision)->toBe(Decision::ALLOW);
        });

    });

});
