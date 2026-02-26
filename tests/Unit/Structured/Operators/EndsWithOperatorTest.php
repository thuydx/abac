<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\EndsWithOperator;

describe('EndsWithOperator (ABAC 1.0)', function () {

    it('returns true when string ends with expected', function () {
        $op = new EndsWithOperator();

        expect($op->compare('ThuyDX', 'DX'))->toBeTrue();
    });

});
