<?php

declare(strict_types=1);

use ThuyDX\ABAC\ValueObjects\Decision;

describe('Decision (ABAC 1.0)', function () {
    it('has correct string values', function () {
        expect(Decision::ALLOW->value)->toBe('allow')
            ->and(Decision::DENY->value)->toBe('deny')
            ->and(Decision::ABSTAIN->value)->toBe('abstain');
    });

    it('creates decision from boolean true', function () {
        $decision = Decision::fromBoolean(true);

        expect($decision)->toBe(Decision::ALLOW);
    });

    it('creates decision from boolean false', function () {
        $decision = Decision::fromBoolean(false);

        expect($decision)->toBe(Decision::ABSTAIN);
    });

    it('can compare enum instances', function () {
        expect(Decision::ALLOW === Decision::ALLOW)->toBeTrue()
            ->and(Decision::DENY === Decision::ABSTAIN)->toBeFalse();
    });

    it('can be created from string using native enum', function () {
        $decision = Decision::from('allow');

        expect($decision)->toBe(Decision::ALLOW);
    });
});
