<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use ThuyDX\ABAC\Contracts\AbacEngineInterface;
use ThuyDX\ABAC\Contracts\ConstraintRepositoryInterface;
use ThuyDX\ABAC\DSL\Evaluator;
use ThuyDX\ABAC\Engine\AbacEngine;
use ThuyDX\ABAC\Infrastructure\Repositories\UserPermissionConstraintRepository;
use ThuyDX\ABAC\Policies\ExpressionPolicy;
use ThuyDX\ABAC\Policies\StructuredPolicy;
use ThuyDX\ABAC\Providers\AbacServiceProvider;

uses(\Tests\TestCase::class);

describe('AbacServiceProvider', function () {

    beforeEach(function () {
        $this->app->register(AbacServiceProvider::class);
    });

    describe('repository binding', function () {

        it('binds ConstraintRepositoryInterface to concrete implementation', function () {

            $repository = $this->app->make(ConstraintRepositoryInterface::class);

            expect($repository)
                ->toBeInstanceOf(UserPermissionConstraintRepository::class);
        });
    });

    describe('evaluator binding', function () {

        it('registers Evaluator as singleton', function () {

            $first  = $this->app->make(Evaluator::class);
            $second = $this->app->make(Evaluator::class);

            expect($first)
                ->toBeInstanceOf(Evaluator::class)
                ->and($first)->toBe($second);
        });
    });

    describe('abac engine binding', function () {

        it('resolves AbacEngineInterface as singleton', function () {

            Config::set('abac.policies', [
                ExpressionPolicy::class,
                StructuredPolicy::class,
            ]);

            $first  = $this->app->make(AbacEngineInterface::class);
            $second = $this->app->make(AbacEngineInterface::class);

            expect($first)
                ->toBeInstanceOf(AbacEngine::class)
                ->and($first)->toBe($second);
        });

        it('injects policies defined in config', function () {

            Config::set('abac.policies', [
                ExpressionPolicy::class,
                StructuredPolicy::class,
            ]);

            $engine = $this->app->make(AbacEngineInterface::class);

            $reflection = new ReflectionClass($engine);
            $property   = $reflection->getProperty('policies');
            $property->setAccessible(true);

            $policies = $property->getValue($engine);

            expect($policies)
                ->toHaveCount(2)
                ->and($policies[0])->toBeInstanceOf(ExpressionPolicy::class)
                ->and($policies[1])->toBeInstanceOf(StructuredPolicy::class);
        });
    });

    describe('config merging', function () {

        it('merges abac config', function () {

            expect(config('abac'))->not->toBeNull();
        });
    });
});
