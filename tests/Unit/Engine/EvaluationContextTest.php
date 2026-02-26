<?php

declare(strict_types=1);

use ThuyDX\ABAC\Engine\EvaluationContext;

describe('EvaluationContext (ABAC 1.0)', function () {

    it('stores constructor values correctly', function () {

        $context = new EvaluationContext(
            userUuid: 'user-123',
            permission: 'post.view',
            attributes: ['age' => 18],
            scope: 'admin',
            module: 'blog'
        );

        expect($context->userUuid)->toBe('user-123')
            ->and($context->permission)->toBe('post.view')
            ->and($context->attributes)->toBe(['age' => 18])
            ->and($context->scope)->toBe('admin')
            ->and($context->module)->toBe('blog');
    });

    describe('variables()', function () {

        it('merges user_uuid into attributes', function () {

            $context = new EvaluationContext(
                userUuid: 'user-123',
                permission: 'post.view',
                attributes: ['age' => 18]
            );

            $vars = $context->variables();

            expect($vars)->toMatchArray([
                'user_uuid' => 'user-123',
                'age' => 18,
            ]);
        });

        it('overrides attribute user_uuid if exists', function () {

            $context = new EvaluationContext(
                userUuid: 'real-id',
                permission: 'post.view',
                attributes: ['user_uuid' => 'fake-id']
            );

            expect($context->variables()['user_uuid'])
                ->toBe('real-id');
        });

    });

});
