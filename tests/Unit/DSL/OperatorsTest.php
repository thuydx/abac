<?php

declare(strict_types=1);

use ThuyDX\ABAC\DSL\Operators;

describe('Operators::compare (ABAC 1.0)', function () {

    it('handles equality', function () {
        expect(Operators::compare(5, '==', 5))->toBeTrue()
            ->and(Operators::compare(5, '!=', 3))->toBeTrue();
    });

    it('handles numeric comparisons', function () {
        expect(Operators::compare(10, '>', 5))->toBeTrue()
            ->and(Operators::compare(5, '<', 10))->toBeTrue();
    });

    it('handles in operator', function () {
        expect(
            Operators::compare('admin', 'in', ['admin', 'editor'])
        )->toBeTrue();
    });

    it('normalizes datetime strings', function () {
        expect(
            Operators::compare(
                '2025-01-01',
                '<',
                '2026-01-01'
            )
        )->toBeTrue();
    });

    it('throws on unsupported operator', function () {
        Operators::compare(1, '???', 2);
    })->throws(RuntimeException::class);

});

describe('Operators::callFunction (ABAC 1.0)', function () {

    it('supports startsWith', function () {
        expect(
            Operators::callFunction('startsWith', ['ThuyDX', 'Thuy'])
        )->toBeTrue();
    });

    it('supports endsWith', function () {
        expect(
            Operators::callFunction('endsWith', ['ThuyDX', 'DX'])
        )->toBeTrue();
    });

    it('supports contains', function () {
        expect(
            Operators::callFunction('contains', ['ThuyDX', 'uyD'])
        )->toBeTrue();
    });

    it('returns DateTimeImmutable for now()', function () {
        $result = Operators::callFunction('now', []);

        expect($result)->toBeInstanceOf(DateTimeImmutable::class);
    });

    it('throws for unknown function', function () {
        Operators::callFunction('unknownFunc', []);
    })->throws(RuntimeException::class);

});
