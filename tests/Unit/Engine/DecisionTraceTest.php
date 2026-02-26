<?php

declare(strict_types=1);

use ThuyDX\ABAC\Engine\DecisionTrace;
use ThuyDX\ABAC\ValueObjects\Decision;

describe('Engine DecisionTrace (ABAC 1.0)', function () {

    describe('add()', function () {

        it('adds a step correctly', function () {

            $trace = new DecisionTrace();

            $trace->add(
                expression: ['type' => 'dsl'],
                policy: 'ExpressionPolicy',
                decision: Decision::ALLOW
            );

            $steps = $trace->all();

            expect($steps)->toHaveCount(1)
                ->and($steps[0])->toMatchArray([
                    'expression' => ['type' => 'dsl'],
                    'policy' => 'ExpressionPolicy',
                    'decision' => 'allow',
                ]);
        });

        it('stores multiple steps in order', function () {

            $trace = new DecisionTrace();

            $trace->add(['a' => 1], 'PolicyA', Decision::ALLOW);
            $trace->add(['b' => 2], 'PolicyB', Decision::DENY);

            $steps = $trace->all();

            expect($steps)->toHaveCount(2)
                ->and($steps[0]['policy'])->toBe('PolicyA')
                ->and($steps[1]['policy'])->toBe('PolicyB')
                ->and($steps[1]['decision'])->toBe('deny');
        });

        it('is mutable (does not create new instance)', function () {

            $trace = new DecisionTrace();

            $original = $trace;

            $trace->add(
                ['x' => 1],
                'PolicyX',
                Decision::ALLOW
            );

            expect($trace)->toBe($original)
                ->and($trace->all())->toHaveCount(1);
        });

    });

    describe('all()', function () {

        it('returns empty array initially', function () {

            $trace = new DecisionTrace();

            expect($trace->all())->toBe([]);
        });

        it('returns full steps array', function () {

            $trace = new DecisionTrace();

            $trace->add(['a' => 1], 'PolicyA', Decision::ALLOW);

            expect($trace->all())
                ->toBeArray()
                ->and($trace->all())->toHaveCount(1);
        });

    });

});
