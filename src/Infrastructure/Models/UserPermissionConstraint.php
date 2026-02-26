<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        'priority',
    ];

    protected $casts = [
        'expression' => 'array',
        'priority'   => 'integer',
    ];

    public function getTable(): string
    {
        return config('abac.table_names.constraints', 'user_permission_constraints');
    }

    protected static function booted(): void
    {
        static::saved(fn ($model) => $model->clearAbacCache());
        static::deleted(fn ($model) => $model->clearAbacCache());
    }

    public function clearAbacCache(): void
    {
        if (! config('abac.cache.enabled')) {
            return;
        }

        $key = sprintf(
            'abac:constraints:%s:%s:%s:%s',
            $this->user_uuid,
            $this->permission,
            $this->scope ?? 'null',
            $this->module ?? 'null'
        );

        Cache::store(config('abac.cache.store'))->forget($key);
    }
}
