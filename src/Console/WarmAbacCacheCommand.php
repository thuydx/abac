<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Console;

use Illuminate\Console\Command;
use ThuyDX\ABAC\Infrastructure\Models\UserPermissionConstraint;

final class WarmAbacCacheCommand extends Command
{
    protected $signature = 'abac:cache-warm {--user=}';

    protected $description = 'Warm ABAC cache for users';

    public function handle(): int
    {
        $user = $this->option('user');

        $query = UserPermissionConstraint::query();

        if ($user) {
            $query->where('user_uuid', $user);
        }

        $query->chunk(100, function ($constraints) {
            foreach ($constraints as $constraint) {
                // Lazy load by triggering repository
                app(\ThuyDX\ABAC\Contracts\ConstraintRepositoryInterface::class)
                    ->forUserAndPermission(
                        $constraint->user_uuid,
                        $constraint->permission,
                        $constraint->scope,
                        $constraint->module
                    );
            }
        });

        $this->info('ABAC cache warmed successfully.');

        return self::SUCCESS;
    }
}
