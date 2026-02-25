<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Structured\Engine;

use InvalidArgumentException;
use ThuyDX\ABAC\Structured\Operators\{BetweenOperator,
    ContainsOperator,
    EndsWithOperator,
    EqualsOperator,
    GreaterOrEqualOperator,
    GreaterThanOperator,
    InOperator,
    LessOrEqualOperator,
    LessThanOperator,
    NotEqualsOperator,
    NotInOperator,
    StartsWithOperator};

final class StructuredOperatorRegistry
{
    protected array $operators;

    public function __construct()
    {
        $this->operators = [
            '='           => new EqualsOperator(),
            '!='          => new NotEqualsOperator(),
            '>'           => new GreaterThanOperator(),
            '>='          => new GreaterOrEqualOperator(),
            '<'           => new LessThanOperator(),
            '<='          => new LessOrEqualOperator(),
            'in'          => new InOperator(),
            'not_in'      => new NotInOperator(),
            'contains'    => new ContainsOperator(),
            'starts_with' => new StartsWithOperator(),
            'ends_with'   => new EndsWithOperator(),
            'between'     => new BetweenOperator(),
        ];
    }

    public function get(string $operator)
    {
        return $this->operators[$operator]
            ?? throw new InvalidArgumentException("Operator [$operator] not supported.");
    }
}
