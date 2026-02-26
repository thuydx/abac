<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\GreaterOrEqualOperator;

describe('GreaterOrEqualOperator (ABAC 1.0)', function () {

    it('returns true when actual is greater or equal', function () {
        $op = new GreaterOrEqualOperator();

        expect($op->compare(10, 5))->toBeTrue()
            ->and($op->compare(5, 5))->toBeTrue();
    });

});
