<?php

declare(strict_types=1);

use ThuyDX\ABAC\Structured\Operators\BetweenOperator;

describe('BetweenOperator (ABAC 1.0)', function () {

    it('returns true when value is within range', function () {
        $op = new BetweenOperator();

        expect($op->compare(5, [1, 10]))->toBeTrue();
    });

    it('returns false when outside range', function () {
        $op = new BetweenOperator();

        expect($op->compare(20, [1, 10]))->toBeFalse();
    });

    it('returns false for invalid expected structure', function () {
        $op = new BetweenOperator();

        expect($op->compare(5, [1]))->toBeFalse();
    });

});
