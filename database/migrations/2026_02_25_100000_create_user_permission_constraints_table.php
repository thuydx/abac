<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_permission_constraints', function (Blueprint $table) {

            $table->uuid()->primary();

            $table->uuid('user_uuid')->index();
            // full slug: site.blog.post.update
            $table->string('permission', 150)->index();
            // scope: system | site
            $table->string('scope', 20)->nullable()->index();
            // module: blog | cms | media ...
            $table->string('module', 50)->nullable()->index();
            $table->unsignedInteger('priority')
                ->default(0)
                ->index();
            // JSON constraints
            $table->json('expression');

            $table->timestamps();

            // Indexing strategy
            $table->index(['user_uuid', 'permission']);
            $table->index(['user_uuid', 'scope', 'module']);
            $table->index(['user_uuid', 'permission', 'scope']);
            $table->index([
                'user_uuid',
                'permission',
                'module',
                'scope',
                'priority',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permission_constraints');
    }
};
