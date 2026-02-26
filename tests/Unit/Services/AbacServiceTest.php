<?php

declare(strict_types=1);

use ThuyDX\ABAC\Contracts\AbacServiceInterface;

describe('AbacService', function () {

    it('allows owner access', function () {

        $service = app(AbacServiceInterface::class);

        $subject  = (object) ['id' => 1];
        $resource = (object) ['user_id' => 1];

        expect(
            $service->check($subject, 'view', $resource)
        )->toBeTrue();
    });
});
