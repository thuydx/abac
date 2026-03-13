<?php

declare(strict_types=1);

use ThuyDX\ABAC\DSL\Tokenizer;

describe('Tokenizer (ABAC 1.0)', function () {

    it('tokenizes comparison expression', function () {
        $tokens = (new Tokenizer('age >= 18'))->tokenize();

        expect($tokens)->toBe([
            'age', '>=', '18',
        ]);
    });

    it('tokenizes logical expression', function () {
        $tokens = (new Tokenizer('age >= 18 && active == true'))->tokenize();

        expect($tokens)->toContain('&&')
            ->and($tokens)->toContain('==');
    });

    it('tokenizes string literals', function () {
        $tokens = (new Tokenizer("name == 'Thuy'"))->tokenize();

        expect($tokens)->toBe([
            'name', '==', 'Thuy',
        ]);
    });

    it('tokenizes array literal', function () {
        $tokens = (new Tokenizer("role in ['admin','editor']"))->tokenize();

        expect($tokens)->toContain('[')
            ->and($tokens)->toContain(']')
            ->and($tokens)->toContain('in');
    });

    it('throws exception for invalid operator', function () {
        (new Tokenizer('age @@ 18'))->tokenize();
    })->throws(RuntimeException::class);

});
