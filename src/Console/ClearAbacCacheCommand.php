<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Console;

use Illuminate\Console\Command;
use ThuyDX\ABAC\Infrastructure\Cache\AbacCacheManager;

final class ClearAbacCacheCommand extends Command
{
    protected $signature = 'abac:cache-clear
                            {--user= : Clear cache for specific user UUID}
                            {--permission= : Clear cache for specific permission}
                            {--rebuild : Rebuild cache after clearing (user only)}
                            {--all : Clear entire ABAC cache}';

    protected $description = 'Clear or rebuild ABAC cache';

    public function handle(AbacCacheManager $cacheManager): int
    {
        $user = $this->option('user');
        $permission = $this->option('permission');
        $all = $this->option('all');
        $rebuild = $this->option('rebuild');

        if ($all) {

            if (! $this->confirm('Are you sure you want to clear ALL ABAC cache?')) {
                $this->info('Operation cancelled.');

                return self::SUCCESS;
            }

            $cacheManager->clearAll();

            $this->info('All ABAC cache cleared successfully.');

            return self::SUCCESS;
        }

        if ($user) {

            $cacheManager->clearUser($user);

            $this->info("ABAC cache cleared for user: {$user}");

            if ($rebuild) {
                $cacheManager->rebuildUser($user);
                $this->info("ABAC cache rebuilt for user: {$user}");
            }

            return self::SUCCESS;
        }

        if ($permission) {

            $cacheManager->clearPermission($permission);

            $this->info("ABAC cache cleared for permission: {$permission}");

            return self::SUCCESS;
        }

        $this->warn('No valid option provided.');
        $this->line('Use one of the following options:');
        $this->line('  --user=UUID');
        $this->line('  --permission=slug');
        $this->line('  --all');

        return self::INVALID;
    }
}
