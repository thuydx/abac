<?php

declare(strict_types=1);

use ThuyDX\ABAC\Infrastructure\Models\UserPermissionConstraint;
use ThuyDX\ABAC\Policies\ExpressionPolicy;
use ThuyDX\ABAC\Policies\StructuredPolicy;

return [

    'enabled' => true,

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
    'cache' => [
        'enabled' => env('ABAC_CACHE_ENABLED', true),
        'ttl'     => env('ABAC_CACHE_TTL', 3600),
        'store'   => env('ABAC_CACHE_STORE', 'redis'), // redis, file, etc
    ],
    'trace' => [
        'enabled' => env('ABAC_TRACE_ENABLED', false),
        'level'   => env('ABAC_TRACE_LEVEL', 'info'), // none|info|debug
        'log'       => env('ABAC_TRACE_LOG', true),
        'log_level' => env('ABAC_TRACE_LOG_LEVEL', 'info'),
        'redis'     => env('ABAC_TRACE_REDIS', true),
        'redis_ttl' => env('ABAC_TRACE_REDIS_TTL', 3600),
    ],
];
