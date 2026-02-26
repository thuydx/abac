<?php

declare(strict_types=1);

use ThuyDX\ABAC\ValueObjects\TraceLevel;

describe('TraceLevel (ABAC 1.0)', function () {

    it('has correct string values', function () {

        expect(TraceLevel::NONE->value)->toBe('none')
            ->and(TraceLevel::INFO->value)->toBe('info')
            ->and(TraceLevel::DEBUG->value)->toBe('debug');
    });

    it('can be created from string', function () {

        $level = TraceLevel::from('debug');

        expect($level)->toBe(TraceLevel::DEBUG);
    });

});
