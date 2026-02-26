<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\InOperator;

describe('InOperator (ABAC 1.0)', function () {

    it('returns true when value is in array', function () {
        $op = new InOperator();

        expect($op->compare('admin', ['admin', 'editor']))
            ->toBeTrue();
    });

    it('uses strict comparison', function () {
        $op = new InOperator();

        expect($op->compare(1, ['1']))->toBeFalse();
    });

});
