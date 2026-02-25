<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\DSL;

use DateTimeImmutable;

final class Operators
{
    public static function compare(
        mixed $left,
        string $operator,
        mixed $right
    ): bool {

        // Normalize DateTime if possible
        $left  = self::normalizeDate($left);
        $right = self::normalizeDate($right);

        return match ($operator) {
            '==' => $left == $right,
            '!=' => $left != $right,
            '>'  => $left > $right,
            '<'  => $left < $right,
            '>=' => $left >= $right,
            '<=' => $left <= $right,
            'in' => in_array($left, (array) $right, true),
            default => throw new \RuntimeException("Unsupported operator")
        };
    }

    public static function callFunction(
        string $name,
        array $args
    ): mixed {

        return match ($name) {

            'startsWith' => str_starts_with(
                (string) $args[0],
                (string) $args[1]
            ),

            'endsWith' => str_ends_with(
                (string) $args[0],
                (string) $args[1]
            ),

            'contains' => str_contains(
                (string) $args[0],
                (string) $args[1]
            ),

            'now' => new DateTimeImmutable(),

            default => throw new \RuntimeException("Unknown function {$name}")
        };
    }

    private static function normalizeDate(mixed $value): mixed
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if (is_string($value)) {
            try {
                return new DateTimeImmutable($value);
            } catch (\Exception) {
                return $value;
            }
        }

        return $value;
    }
}
