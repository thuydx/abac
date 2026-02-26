<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\LessOrEqualOperator;

describe('LessOrEqualOperator (ABAC 1.0)', function () {

    it('returns true when actual is less or equal', function () {
        $op = new LessOrEqualOperator();

        expect($op->compare(5, 10))->toBeTrue()
            ->and($op->compare(5, 5))->toBeTrue();
    });

});
