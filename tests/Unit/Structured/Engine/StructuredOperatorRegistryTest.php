<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Engine\StructuredOperatorRegistry;

describe('StructuredOperatorRegistry (ABAC 1.0)', function () {

    it('returns operator instance for supported operators', function () {
        $registry = new StructuredOperatorRegistry();

        expect($registry->get('='))->not->toBeNull()
            ->and($registry->get('!='))->not->toBeNull()
            ->and($registry->get('>'))->not->toBeNull()
            ->and($registry->get('>='))->not->toBeNull()
            ->and($registry->get('<'))->not->toBeNull()
            ->and($registry->get('<='))->not->toBeNull()
            ->and($registry->get('in'))->not->toBeNull()
            ->and($registry->get('not_in'))->not->toBeNull()
            ->and($registry->get('contains'))->not->toBeNull()
            ->and($registry->get('starts_with'))->not->toBeNull()
            ->and($registry->get('ends_with'))->not->toBeNull()
            ->and($registry->get('between'))->not->toBeNull();
    });

    it('throws exception for unsupported operator', function () {
        $registry = new StructuredOperatorRegistry();

        $registry->get('invalid_operator');
    })->throws(InvalidArgumentException::class);

});
