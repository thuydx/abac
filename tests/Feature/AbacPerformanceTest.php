<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ThuyDX\ABAC\Contracts\ConstraintRepositoryInterface;
use ThuyDX\ABAC\Contracts\PolicyInterface;
use ThuyDX\ABAC\DSL\Evaluator;
use ThuyDX\ABAC\Engine\AbacEngine;
use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\Infrastructure\Models\UserPermissionConstraint;
use ThuyDX\ABAC\Infrastructure\Repositories\UserPermissionConstraintRepository;
use ThuyDX\ABAC\Policies\ExpressionPolicy;
use ThuyDX\ABAC\ValueObjects\Decision;

uses(TestCase::class);
uses(RefreshDatabase::class);

describe('ABAC Performance', function () {
    it('evaluates 10k constraints fast (no DB)', function () {

        $repository = Mockery::mock(ConstraintRepositoryInterface::class);
        $policy = Mockery::mock(PolicyInterface::class);

        $constraints = [];

        for ($i = 0; $i < 10000; $i++) {
            $constraints[] = [
                'type' => 'dsl',
                'priority' => $i,
            ];
        }

        $repository->shouldReceive('forUserAndPermission')
            ->andReturn($constraints);

        $policy->shouldReceive('supports')->andReturn(true);
        $policy->shouldReceive('evaluate')
            ->andReturn(Decision::ALLOW);

        $engine = new AbacEngine($repository, [$policy]);

        $context = new EvaluationContext('u1', 'post.view');

        $start = microtime(true);
        $engine->decide($context);
        $duration = round(microtime(true) - $start, 3);

        echo "\n  ✓ Engine execution: {$duration} sec\n";

        expect($duration)->toBeLessThan(1.0);
    });
    it('evaluates 10k constraints WITHOUT sort (same priority)', function () {

        Config::set('abac.trace.enabled', false);

        $repository = Mockery::mock(ConstraintRepositoryInterface::class);
        $policy = Mockery::mock(PolicyInterface::class);

        // All constraints have same priority → minimal sorting work
        $constraints = array_fill(0, 10000, [
            'type' => 'dsl',
            'priority' => 1,
        ]);

        $repository->shouldReceive('forUserAndPermission')
            ->andReturn($constraints);

        $policy->shouldReceive('supports')->andReturn(true);
        $policy->shouldReceive('evaluate')
            ->andReturn(Decision::ALLOW);

        $engine = new AbacEngine($repository, [$policy]);

        $context = new EvaluationContext('u1', 'post.view');

        $start = microtime(true);
        $engine->decide($context);
        $duration = round(microtime(true) - $start, 3);
        echo "  ✓ No-sort execution: {$duration} sec\n";

        expect($duration)->toBeLessThan(1.0);
    });

    it('evaluates 10k constraints WITHOUT trace enabled', function () {

        Config::set('abac.trace.enabled', false);

        $repository = Mockery::mock(ConstraintRepositoryInterface::class);
        $policy = Mockery::mock(PolicyInterface::class);

        $constraints = [];

        for ($i = 0; $i < 10000; $i++) {
            $constraints[] = [
                'type' => 'dsl',
                'priority' => $i,
            ];
        }

        $repository->shouldReceive('forUserAndPermission')
            ->andReturn($constraints);

        $policy->shouldReceive('supports')->andReturn(true);
        $policy->shouldReceive('evaluate')
            ->andReturn(Decision::ALLOW);

        $engine = new AbacEngine($repository, [$policy]);

        $context = new EvaluationContext('u1', 'post.view');

        $start = microtime(true);
        $engine->decide($context);
        $duration = round(microtime(true) - $start, 3);
        echo "  ✓ No-trace execution: {$duration} sec\n";

        expect($duration)->toBeLessThan(1.2);
    });

    it('evaluates 1k constraints under acceptable time', function () {

        $policy = new ExpressionPolicy(new Evaluator());

        $engine = new AbacEngine(
            new UserPermissionConstraintRepository(),
            [$policy]
        );

        for ($i = 0; $i < 1000; $i++) {
            UserPermissionConstraint::create([
                'user_uuid' => 'u1',
                'permission' => 'post.view',
                'expression' => [
                    'type' => 'dsl',
                    'expression' => 'age >= 18',
                    'priority' => rand(1, 100),
                ],
                'scope' => null,
                'module' => null,
            ]);
        }

        $context = new EvaluationContext(
            userUuid  : 'u1',
            permission: 'post.view',
            attributes: ['age' => 20]
        );

        $start = microtime(true);

        $engine->evaluate($context);

        $duration = round(microtime(true) - $start, 3);

        echo "  ✓ Execution time: {$duration} sec\n";

        expect($duration)->toBeLessThan(5.0);
    });
});
