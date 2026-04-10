<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use ThuyDX\ABAC\Infrastructure\Models\UserPermissionConstraint;

uses(\Tests\TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    // đảm bảo table name đúng khi test
    Config::set('abac.table_names.constraints', 'user_permission_constraints');
});
describe('Model: UserPermissionConstraint (ABAC 1.0)', function () {
    it('uses uuid as primary key and not incrementing', function () {
        $model = new UserPermissionConstraint();

        expect($model->getKeyName())->toBe('uuid')
            ->and($model->getIncrementing())->toBeFalse()
            ->and($model->getKeyType())->toBe('string');
    });

    it('casts expression to array', function () {
        $constraint = UserPermissionConstraint::create([
            'user_uuid' => 'user-1',
            'permission' => 'post.view',
            'module' => 'blog',
            'expression' => ['department' => 'IT'],
            'scope' => 'admin',
        ]);

        expect($constraint->expression)
            ->toBeArray()
            ->toMatchArray(['department' => 'IT']);
    });

    it('uses table name from config', function () {
        Config::set('abac.table_names.constraints', 'custom_constraints');

        $model = new UserPermissionConstraint();

        expect($model->getTable())->toBe('custom_constraints');
    });
});
