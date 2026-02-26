<?php

declare(strict_types=1);

use ThuyDX\ABAC\ValueObjects\Decision;
use ThuyDX\ABAC\ValueObjects\DecisionTrace;
use ThuyDX\ABAC\ValueObjects\TraceLevel;

describe('DecisionTrace (ABAC 1.0)', function () {

    describe('start()', function () {

        it('creates trace with generated correlation id', function () {

            $trace = DecisionTrace::start();

            expect($trace->correlationId())
                ->toBeString()
                ->and(strlen($trace->correlationId()))->toBeGreaterThan(0);
        });

        it('uses provided correlation id', function () {

            $trace = DecisionTrace::start(
                correlationId: 'request-123',
                level: TraceLevel::INFO
            );

            expect($trace->correlationId())
                ->toBe('request-123');
        });

    });

    describe('isEnabled()', function () {

        it('returns false when level is NONE', function () {

            $trace = DecisionTrace::start(
                level: TraceLevel::NONE
            );

            expect($trace->isEnabled())->toBeFalse();
        });

        it('returns true when level is INFO', function () {

            $trace = DecisionTrace::start(
                level: TraceLevel::INFO
            );

            expect($trace->isEnabled())->toBeTrue();
        });

    });

    describe('add()', function () {

        it('does not mutate original instance (immutable)', function () {

            $trace = DecisionTrace::start(level: TraceLevel::INFO);

            $newTrace = $trace->add(
                expression: ['type' => 'dsl'],
                policyClass: 'ExpressionPolicy',
                decision: Decision::ALLOW,
                priority: 10,
                startTime: microtime(true)
            );

            expect($trace->all())->toHaveCount(0)
                ->and($newTrace->all())->toHaveCount(1);
        });

        it('adds step with correct structure', function () {

            $trace = DecisionTrace::start(level: TraceLevel::INFO);

            $start = microtime(true);
            usleep(1000); // simulate small delay

            $trace = $trace->add(
                expression: ['type' => 'dsl'],
                policyClass: 'ExpressionPolicy',
                decision: Decision::ALLOW,
                priority: 5,
                startTime: $start
            );

            $step = $trace->all()[0];

            expect($step)
                ->toHaveKeys([
                    'timestamp',
                    'priority',
                    'policy',
                    'decision',
                    'duration_ms',
                    'expression',
                ])
                ->and($step['priority'])->toBe(5)
                ->and($step['policy'])->toBe('ExpressionPolicy')
                ->and($step['decision'])->toBe('allow')
                ->and($step['duration_ms'])->toBeFloat();
        });

        it('hides expression when level is INFO', function () {

            $trace = DecisionTrace::start(level: TraceLevel::INFO);

            $trace = $trace->add(
                expression: ['secret' => true],
                policyClass: 'ExpressionPolicy',
                decision: Decision::ALLOW,
                priority: 1,
                startTime: microtime(true)
            );

            expect($trace->all()[0]['expression'])
                ->toBe([]);
        });

        it('includes expression when level is DEBUG', function () {

            $trace = DecisionTrace::start(level: TraceLevel::DEBUG);

            $expression = ['secret' => true];

            $trace = $trace->add(
                expression: $expression,
                policyClass: 'ExpressionPolicy',
                decision: Decision::ALLOW,
                priority: 1,
                startTime: microtime(true)
            );

            expect($trace->all()[0]['expression'])
                ->toBe($expression);
        });

        it('returns same instance when level is NONE', function () {

            $trace = DecisionTrace::start(level: TraceLevel::NONE);

            $newTrace = $trace->add(
                expression: ['x' => 1],
                policyClass: 'ExpressionPolicy',
                decision: Decision::ALLOW,
                priority: 1,
                startTime: microtime(true)
            );

            expect($newTrace)->toBe($trace)
                ->and($trace->all())->toHaveCount(0);
        });

    });

});
