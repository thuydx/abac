<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;

final class AbacCacheManager
{
    public function clearUser(string $userUuid): void
    {
        Cache::store(config('abac.cache.store'))
            ->tags(['user:'.$userUuid])
            ->flush();
    }

    public function clearPermission(string $permission): void
    {
        Cache::store(config('abac.cache.store'))
            ->tags(['permission:'.$permission])
            ->flush();
    }

    public function clearAll(): void
    {
        Cache::store(config('abac.cache.store'))
            ->tags(['abac'])
            ->flush();
    }

    public function rebuildUser(string $userUuid): void
    {
        // Just clear. It will rebuild lazily on next request.
        $this->clearUser($userUuid);
    }
}
