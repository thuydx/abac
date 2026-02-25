<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permission_constraints', function (Blueprint $table) {

            $table->uuid('uuid')->primary();

            $table->uuid('user_uuid');

            // scope: system | site
            $table->string('scope', 20);

            // module: blog | cms | media ...
            $table->string('module', 50);

            // full slug: site.blog.post.update
            $table->string('permission', 150);

            // JSON constraints
            $table->json('expression');

            $table->timestamps();

            // Indexing strategy
            $table->index(['user_uuid', 'permission']);
            $table->index(['user_uuid', 'scope', 'module']);
            $table->index(['user_uuid', 'permission', 'scope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permission_constraints');
    }
};
