<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use ThuyDX\ABAC\DSL\Evaluator;
use ThuyDX\ABAC\Engine\AbacEngine;
use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\Infrastructure\Models\UserPermissionConstraint;
use ThuyDX\ABAC\Policies\ExpressionPolicy;
use ThuyDX\ABAC\Policies\StructuredPolicy;
use ThuyDX\ABAC\Structured\Engine\StructuredEvaluator;
use ThuyDX\ABAC\Structured\Engine\StructuredOperatorRegistry;

uses(\Tests\TestCase::class);
uses(RefreshDatabase::class);

describe('ABAC Integration (DSL + Structured)', function () {

    beforeEach(function () {

        Config::set('abac.table_names.constraints', 'user_permission_constraints');

        $dslPolicy = new ExpressionPolicy(new Evaluator());

        $structuredPolicy = new StructuredPolicy(
            new StructuredEvaluator(new StructuredOperatorRegistry())
        );

        $this->engine = new AbacEngine(
            new \ThuyDX\ABAC\Infrastructure\Repositories\UserPermissionConstraintRepository(),
            [$dslPolicy, $structuredPolicy]
        );
    });

    it('allows access when DSL constraint passes', function () {

        UserPermissionConstraint::create([
            'user_uuid' => 'u1',
            'permission' => 'post.view',
            'expression' => [
                'type' => 'dsl',
                'expression' => 'age >= 18',
                'priority' => 10,
            ],
            'scope' => null,
            'module' => null,
        ]);

        $context = new EvaluationContext(
            userUuid: 'u1',
            permission: 'post.view',
            attributes: ['age' => 20]
        );

        expect($this->engine->evaluate($context))->toBeTrue();
    });

    it('allows access when Structured rule passes', function () {

        UserPermissionConstraint::create([
            'user_uuid' => 'u2',
            'permission' => 'post.edit',
            'expression' => [
                'type' => 'structured',
                'decision' => 'allow',
                'rules' => [
                    'field' => 'role',
                    'operator' => '=',
                    'value' => 'admin',
                ],
                'priority' => 5,
            ],
            'scope' => null,
            'module' => null,
        ]);

        $context = new EvaluationContext(
            userUuid: 'u2',
            permission: 'post.edit',
            attributes: ['role' => 'admin']
        );

        expect($this->engine->evaluate($context))->toBeTrue();
    });

});
