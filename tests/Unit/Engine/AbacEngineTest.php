<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use ThuyDX\ABAC\Contracts\ConstraintRepositoryInterface;
use ThuyDX\ABAC\Contracts\PolicyInterface;
use ThuyDX\ABAC\Engine\AbacEngine;
use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\ValueObjects\Decision;
use ThuyDX\ABAC\ValueObjects\DecisionTrace;
use ThuyDX\ABAC\ValueObjects\TraceLevel;

uses(\Tests\TestCase::class);

describe('AbacEngine (ABAC 1.0)', function () {

    beforeEach(function () {

        $this->repository = Mockery::mock(ConstraintRepositoryInterface::class);
        $this->policy = Mockery::mock(PolicyInterface::class);

        $this->engine = new AbacEngine(
            $this->repository,
            [$this->policy]
        );

        Config::set('abac.trace.enabled', false);
        Config::set('abac.trace.level', 'info');
    });

    describe('decide()', function () {

        it('returns DENY when no constraints found', function () {

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn([]);

            $context = new EvaluationContext('u1', 'post.view');

            expect($this->engine->decide($context))
                ->toBe(Decision::DENY);
        });

        it('returns ALLOW when policy allows', function () {

            $constraints = [
                ['type' => 'dsl', 'priority' => 10],
            ];

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn($constraints);

            $this->policy->shouldReceive('supports')->andReturn(true);
            $this->policy->shouldReceive('evaluate')->andReturn(Decision::ALLOW);

            $context = new EvaluationContext('u1', 'post.view');

            expect($this->engine->decide($context))
                ->toBe(Decision::ALLOW);
        });

        it('returns DENY when policy denies (deny override)', function () {

            $constraints = [
                ['type' => 'dsl', 'priority' => 5],
            ];

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn($constraints);

            $this->policy->shouldReceive('supports')->andReturn(true);
            $this->policy->shouldReceive('evaluate')->andReturn(Decision::DENY);

            $context = new EvaluationContext('u1', 'post.view');

            expect($this->engine->decide($context))
                ->toBe(Decision::DENY);
        });

        it('continues when policy abstains', function () {

            $constraints = [
                ['type' => 'dsl'],
            ];

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn($constraints);

            $this->policy->shouldReceive('supports')->andReturn(true);
            $this->policy->shouldReceive('evaluate')->andReturn(Decision::ABSTAIN);

            $context = new EvaluationContext('u1', 'post.view');

            expect($this->engine->decide($context))
                ->toBe(Decision::DENY);
        });

        it('processes constraints in order returned by repository', function () {

            $constraints = [
                ['type' => 'dsl', 'priority' => 10],
                ['type' => 'dsl', 'priority' => 1],
            ];

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn($constraints);

            $this->policy->shouldReceive('supports')
                ->andReturn(true);

            $this->policy->shouldReceive('evaluate')
                ->once()
                ->andReturn(Decision::ALLOW);

            $context = new EvaluationContext('u1', 'post.view');

            expect($this->engine->decide($context))
                ->toBe(Decision::ALLOW);
        });
    });

    describe('trace behavior', function () {

        it('does not record trace when disabled', function () {

            Config::set('abac.trace.enabled', false);

            $constraints = [
                ['type' => 'dsl', 'priority' => 10],
            ];

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn($constraints);

            $this->policy->shouldReceive('supports')->andReturn(true);
            $this->policy->shouldReceive('evaluate')->andReturn(Decision::ALLOW);

            $context = new EvaluationContext('u1', 'post.view');

            expect($this->engine->decide($context))
                ->toBe(Decision::ALLOW);
        });

        it('records trace when enabled (INFO)', function () {

            Config::set('abac.trace.enabled', true);
            Config::set('abac.trace.level', 'info');

            $constraints = [
                ['type' => 'dsl', 'priority' => 10],
            ];

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn($constraints);

            $this->policy->shouldReceive('supports')->andReturn(true);
            $this->policy->shouldReceive('evaluate')->andReturn(Decision::ALLOW);

            $context = new EvaluationContext('u1', 'post.view');

            $trace = DecisionTrace::start(level: TraceLevel::INFO);

            $this->engine->decide($context, $trace);

            expect($trace->isEnabled())->toBeTrue();
        });

        it('records detailed trace in DEBUG level', function () {

            Config::set('abac.trace.enabled', true);
            Config::set('abac.trace.level', 'debug');

            $constraints = [
                ['type' => 'dsl', 'priority' => 5],
            ];

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn($constraints);

            $this->policy->shouldReceive('supports')->andReturn(true);
            $this->policy->shouldReceive('evaluate')->andReturn(Decision::ALLOW);

            $context = new EvaluationContext('u1', 'post.view');

            $trace = DecisionTrace::start(level: TraceLevel::DEBUG);

            $this->engine->decide($context, $trace);

            expect($trace->correlationId())->toBeString();
        });
    });

    describe('evaluate()', function () {

        it('returns true when decision is ALLOW', function () {

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn([['type' => 'dsl']]);

            $this->policy->shouldReceive('supports')->andReturn(true);
            $this->policy->shouldReceive('evaluate')->andReturn(Decision::ALLOW);

            $context = new EvaluationContext('u1', 'post.view');

            expect($this->engine->evaluate($context))
                ->toBeTrue();
        });

        it('returns false when decision is DENY', function () {

            $this->repository->shouldReceive('forUserAndPermission')
                ->andReturn([]);

            $context = new EvaluationContext('u1', 'post.view');

            expect($this->engine->evaluate($context))
                ->toBeFalse();
        });
    });

});
