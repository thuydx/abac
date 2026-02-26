<?php

declare(strict_types=1);

use ThuyDX\ABAC\DSL\Evaluator;

describe('Evaluator (ABAC 1.0)', function () {

    it('evaluates simple comparison', function () {
        $result = (new Evaluator())->evaluate(
            "age >= 18",
            ['age' => 20]
        );

        expect($result)->toBeTrue();
    });

    it('evaluates logical and', function () {
        $result = (new Evaluator())->evaluate(
            "age >= 18 && active == true",
            ['age' => 20, 'active' => true]
        );

        expect($result)->toBeTrue();
    });

    it('evaluates logical or', function () {
        $result = (new Evaluator())->evaluate(
            "age >= 18 || active == true",
            ['age' => 15, 'active' => true]
        );

        expect($result)->toBeTrue();
    });

    it('evaluates not operator', function () {
        $result = (new Evaluator())->evaluate(
            "! active",
            ['active' => false]
        );

        expect($result)->toBeTrue();
    });

    it('evaluates in operator', function () {
        $result = (new Evaluator())->evaluate(
            "role in ['admin','editor']",
            ['role' => 'admin']
        );

        expect($result)->toBeTrue();
    });

    it('evaluates function call', function () {
        $result = (new Evaluator())->evaluate(
            "startsWith(name,'Thuy')",
            ['name' => 'ThuyDX']
        );

        expect($result)->toBeTrue();
    });
});
