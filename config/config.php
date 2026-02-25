<?php

declare(strict_types=1);

use ThuyDX\ABAC\Infrastructure\Models\UserPermissionConstraint;
use ThuyDX\ABAC\Policies\ExpressionPolicy;
use ThuyDX\ABAC\Structured\StructuredPolicy;

return [

    'enabled' => true,

    'cache' => [
        'enabled' => false,
        'ttl'     => 3600,
    ],

    'models' => [
        'constraint' => UserPermissionConstraint::class,
    ],

    'table_names' => [
        'constraints' => 'user_permission_constraints',
    ],
    'policies'    => [
        StructuredPolicy::class,
        ExpressionPolicy::class,
    ],
];
