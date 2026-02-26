<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\ContainsOperator;

describe('ContainsOperator (ABAC 1.0)', function () {

    it('returns true when substring exists', function () {
        $op = new ContainsOperator();

        expect($op->compare('ThuyDX', 'uyD'))->toBeTrue();
    });

});
