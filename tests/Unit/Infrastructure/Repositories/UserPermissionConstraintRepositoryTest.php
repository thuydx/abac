<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use ThuyDX\ABAC\Infrastructure\Models\UserPermissionConstraint;
use ThuyDX\ABAC\Infrastructure\Repositories\UserPermissionConstraintRepository;

uses(\Tests\TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('abac.table_names.constraints', 'user_permission_constraints');
});
describe('Repository: UserPermissionConstraintRepository (ABAC 1.0)', function () {
    it('returns expressions for specific user and permission', function () {
        UserPermissionConstraint::create([
            'user_uuid'  => 'user-1',
            'permission' => 'post.view',
            'module'     => 'blog',
            'expression' => ['department' => 'IT'],
            'scope'      => 'admin',
        ]);

        UserPermissionConstraint::create([
            'user_uuid'  => 'user-1',
            'permission' => 'post.view',
            'module'     => 'blog',
            'expression' => ['department' => 'HR'],
            'scope'      => 'admin',
        ]);

        $repo = new UserPermissionConstraintRepository();

        $results = $repo->forUserAndPermission(
            'user-1',
            'post.view',
            'admin',
            'blog'
        );

        expect($results)
            ->toHaveCount(2)
            ->and($results[0])->toBeArray();
    });

    it('filters by scope when provided', function () {
        UserPermissionConstraint::create([
            'user_uuid'  => 'user-1',
            'permission' => 'post.edit',
            'module'     => 'blog',
            'expression' => ['level' => 1],
            'scope'      => 'admin',
        ]);

        UserPermissionConstraint::create([
            'user_uuid'  => 'user-1',
            'permission' => 'post.edit',
            'module'     => 'blog',
            'expression' => ['level' => 2],
            'scope'      => 'user',
        ]);

        $repo = new UserPermissionConstraintRepository();

        $results = $repo->forUserAndPermission(
            'user-1',
            'post.edit',
            'admin',
            'blog'
        );

        expect($results)
            ->toHaveCount(1)
            ->and($results[0])->toMatchArray(['level' => 1]);
    });

    it('filters by module when provided', function () {
        UserPermissionConstraint::create([
            'user_uuid'  => 'user-1',
            'permission' => 'post.delete',
            'module'     => 'blog',
            'expression' => ['allowed' => true],
            'scope'      => 'system', // ✅ không còn null
        ]);

        UserPermissionConstraint::create([
            'user_uuid'  => 'user-1',
            'permission' => 'post.delete',
            'module'     => 'cms',
            'expression' => ['allowed' => false],
            'scope'      => 'system',
        ]);

        $repo = new UserPermissionConstraintRepository();

        $results = $repo->forUserAndPermission(
            'user-1',
            'post.delete',
            'system',
            'blog'
        );

        expect($results)
            ->toHaveCount(1)
            ->and($results[0])->toMatchArray(['allowed' => true]);
    });

    it('returns all expressions for user', function () {
        UserPermissionConstraint::create([
            'user_uuid'  => 'user-2',
            'permission' => 'post.view',
            'module'     => 'blog',
            'expression' => ['a' => 1],
            'scope'      => 'system',
        ]);

        UserPermissionConstraint::create([
            'user_uuid'  => 'user-2',
            'permission' => 'post.edit',
            'module'     => 'blog',
            'expression' => ['b' => 2],
            'scope'      => 'system',
        ]);

        $repo = new UserPermissionConstraintRepository();

        $results = $repo->forUser('user-2');

        expect($results)
            ->toHaveCount(2)
            ->and($results[0])->toBeArray()
            ->and($results[1])->toBeArray();
    });

    it('returns empty array when no constraint found', function () {
        $repo = new UserPermissionConstraintRepository();

        $results = $repo->forUserAndPermission('missing-user', 'missing-permission', 'missing-scope');

        expect($results)->toBeArray()->toBeEmpty();
    });
});
