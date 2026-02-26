<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\GreaterThanOperator;

describe('GreaterThanOperator (ABAC 1.0)', function () {

    it('returns true when actual is greater', function () {
        $op = new GreaterThanOperator();

        expect($op->compare(10, 5))->toBeTrue();
    });

    it('returns false otherwise', function () {
        $op = new GreaterThanOperator();

        expect($op->compare(5, 10))->toBeFalse();
    });

});
