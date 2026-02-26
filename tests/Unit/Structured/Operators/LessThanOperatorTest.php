<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\LessThanOperator;

describe('LessThanOperator (ABAC 1.0)', function () {

    it('returns true when actual is less', function () {
        $op = new LessThanOperator();

        expect($op->compare(5, 10))->toBeTrue();
    });

});
