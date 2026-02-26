<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\StartsWithOperator;

describe('StartsWithOperator (ABAC 1.0)', function () {

    it('returns true when string starts with expected', function () {
        $op = new StartsWithOperator();

        expect($op->compare('ThuyDX', 'Thuy'))->toBeTrue();
    });

});
