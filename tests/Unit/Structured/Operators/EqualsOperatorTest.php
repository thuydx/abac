<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\EqualsOperator;

describe('EqualsOperator (ABAC 1.0)', function () {

    it('returns true when values are equal', function () {
        $op = new EqualsOperator();

        expect($op->compare(5, 5))->toBeTrue();
    });

    it('returns false when values are not equal', function () {
        $op = new EqualsOperator();

        expect($op->compare(5, 3))->toBeFalse();
    });

});
