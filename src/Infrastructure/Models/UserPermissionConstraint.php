<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

final class UserPermissionConstraint extends Model
{
    use HasUuids;

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_uuid',
        'permission',
        'module',
        'expression',
        'scope',
    ];

    protected $casts = [
        'expression' => 'array',
    ];

    public function getTable(): string
    {
        return config('abac.table_names.constraints', 'user_permission_constraints');
    }
}
