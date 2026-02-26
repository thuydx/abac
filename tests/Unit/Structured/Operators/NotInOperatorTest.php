<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\NotInOperator;

describe('NotInOperator (ABAC 1.0)', function () {

    it('returns true when value is not in array', function () {
        $op = new NotInOperator();

        expect($op->compare('guest', ['admin', 'editor']))
            ->toBeTrue();
    });

});
