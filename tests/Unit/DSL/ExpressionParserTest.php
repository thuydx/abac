<?php

declare(strict_types=1);

use ThuyDX\ABAC\DSL\ExpressionParser;
use ThuyDX\ABAC\DSL\Tokenizer;

describe('ExpressionParser (ABAC 1.0)', function () {

    it('parses simple comparison', function () {
        $tokens = (new Tokenizer("age >= 18"))->tokenize();
        $ast = (new ExpressionParser($tokens))->parse();

        expect($ast[0])->toBe('compare')
            ->and($ast[1])->toBe('>=');
    });

    it('parses logical and', function () {
        $tokens = (new Tokenizer("a == 1 && b == 2"))->tokenize();
        $ast = (new ExpressionParser($tokens))->parse();

        expect($ast[0])->toBe('and');
    });

    it('parses logical or', function () {
        $tokens = (new Tokenizer("a == 1 || b == 2"))->tokenize();
        $ast = (new ExpressionParser($tokens))->parse();

        expect($ast[0])->toBe('or');
    });

    it('parses not expression', function () {
        $tokens = (new Tokenizer("! active"))->tokenize();
        $ast = (new ExpressionParser($tokens))->parse();

        expect($ast[0])->toBe('not');
    });

});
