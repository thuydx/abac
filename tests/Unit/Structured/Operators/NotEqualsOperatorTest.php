<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\NotEqualsOperator;

describe('NotEqualsOperator (ABAC 1.0)', function () {

    it('returns true when values differ', function () {
        $op = new NotEqualsOperator();

        expect($op->compare(5, 3))->toBeTrue();
    });

    it('returns false when values are equal', function () {
        $op = new NotEqualsOperator();

        expect($op->compare(5, 5))->toBeFalse();
    });

});
