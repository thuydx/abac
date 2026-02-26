<?php

declare(strict_types=1);

use ThuyDX\ABAC\Engine\EvaluationContext;
use ThuyDX\ABAC\Structured\Engine\StructuredEvaluator;
use ThuyDX\ABAC\Structured\Engine\StructuredOperatorRegistry;

describe('StructuredEvaluator (ABAC 1.0)', function () {

    beforeEach(function () {
        $this->registry  = new StructuredOperatorRegistry();
        $this->evaluator = new StructuredEvaluator($this->registry);
    });

    describe('leaf condition evaluation', function () {

        it('evaluates equals condition', function () {
            $rule = [
                'field'    => 'age',
                'operator' => '=',
                'value'    => 18,
            ];

            $context = new EvaluationContext(
                userUuid  : 'user-1',
                permission: 'post.view',
                attributes: ['age' => 18]
            );

            expect($this->evaluator->evaluate($rule, $context))
                ->toBeTrue();
        });

        it('evaluates greater than condition', function () {
            $rule = [
                'field'    => 'age',
                'operator' => '>',
                'value'    => 18,
            ];

            $context = new EvaluationContext(
                userUuid  : 'user-1',
                permission: 'post.view',
                attributes: ['age' => 20]
            );

            expect($this->evaluator->evaluate($rule, $context))
                ->toBeTrue();
        });
    });

    describe('logical grouping', function () {

        it('evaluates AND group', function () {
            $rule = [
                'operator'   => 'and',
                'conditions' => [
                    [
                        'field'    => 'age',
                        'operator' => '>=',
                        'value'    => 18,
                    ],
                    [
                        'field'    => 'active',
                        'operator' => '=',
                        'value'    => true,
                    ],
                ],
            ];

            $context = new EvaluationContext(
                userUuid  : 'user-1',
                permission: 'post.view',
                attributes: [
                    'age'    => 20,
                    'active' => true,
                ]
            );

            expect($this->evaluator->evaluate($rule, $context))
                ->toBeTrue();
        });

        it('evaluates OR group', function () {
            $rule = [
                'operator'   => 'or',
                'conditions' => [
                    [
                        'field'    => 'age',
                        'operator' => '<',
                        'value'    => 18,
                    ],
                    [
                        'field'    => 'role',
                        'operator' => '=',
                        'value'    => 'admin',
                    ],
                ],
            ];

            $context = new EvaluationContext(
                userUuid  : 'user-1',
                permission: 'post.view',
                attributes: [
                    'age'  => 15,
                    'role' => 'admin',
                ]
            );

            expect($this->evaluator->evaluate($rule, $context))
                ->toBeTrue();
        });
    });

    describe('dynamic value resolution', function () {

        it('resolves user_uuid dynamically', function () {
            $rule = [
                'field'    => 'owner_id',
                'operator' => '=',
                'value'    => 'user_uuid',
            ];

            $context = new EvaluationContext(
                userUuid  : 'abc-123',
                permission: 'post.edit',
                attributes: [
                    'owner_id' => 'abc-123',
                ]
            );

            expect($this->evaluator->evaluate($rule, $context))
                ->toBeTrue();
        });
    });

    describe('nested conditions', function () {

        it('evaluates nested AND/OR structure', function () {
            $rule = [
                'operator'   => 'and',
                'conditions' => [
                    [
                        'field'    => 'age',
                        'operator' => '>=',
                        'value'    => 18,
                    ],
                    [
                        'operator'   => 'or',
                        'conditions' => [
                            [
                                'field'    => 'role',
                                'operator' => '=',
                                'value'    => 'admin',
                            ],
                            [
                                'field'    => 'role',
                                'operator' => '=',
                                'value'    => 'editor',
                            ],
                        ],
                    ],
                ],
            ];

            $context = new EvaluationContext(
                userUuid  : 'user-1',
                permission: 'post.view',
                attributes: [
                    'age'  => 20,
                    'role' => 'editor',
                ]
            );

            expect($this->evaluator->evaluate($rule, $context))
                ->toBeTrue();
        });
    });
});
