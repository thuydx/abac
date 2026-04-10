<?php

declare(strict_types=1);

use ThuyDX\ABAC\ValueObjects\AttributeBag;

describe('AttributeBag (ABAC 1.0)', function () {
    it('returns value by key', function () {
        $bag = new AttributeBag([
            'department' => 'IT',
            'level' => 5,
        ]);

        expect($bag->get('department'))->toBe('IT')
            ->and($bag->get('level'))->toBe(5);
    });

    it('returns null when key does not exist', function () {
        $bag = new AttributeBag([
            'name' => 'Thuy',
        ]);

        expect($bag->get('missing'))->toBeNull();
    });

    it('returns all attributes', function () {
        $attributes = [
            'a' => 1,
            'b' => 2,
        ];

        $bag = new AttributeBag($attributes);

        expect($bag->all())
            ->toBeArray()
            ->toMatchArray($attributes);
    });

    it('handles empty attribute bag', function () {
        $bag = new AttributeBag([]);

        expect($bag->all())->toBeEmpty()
            ->and($bag->get('anything'))->toBeNull();
    });
});
