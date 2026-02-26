<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\DSL;

use RuntimeException;

final class ExpressionParser
{
    private array $tokens;

    private int $position = 0;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function parse(): mixed
    {
        $result = $this->parseOr();

        if ($this->position < count($this->tokens)) {
            throw new RuntimeException('Unexpected token');
        }

        return $result;
    }

    private function parseOr(): mixed
    {
        $left = $this->parseAnd();

        while ($this->match('||')) {
            $right = $this->parseAnd();
            $left = ['or', $left, $right];
        }

        return $left;
    }

    private function parseAnd(): mixed
    {
        $left = $this->parseUnary();

        while ($this->match('&&')) {
            $right = $this->parseUnary();
            $left = ['and', $left, $right];
        }

        return $left;
    }

    private function parseUnary(): mixed
    {
        if ($this->match('!')) {
            return ['not', $this->parseUnary()];
        }

        return $this->parseComparison();
    }

    private function parseComparison(): mixed
    {
        $left = $this->parsePrimary();

        if ($this->match('==', '!=', '>', '<', '>=', '<=', 'in')) {
            $operator = $this->previous();
            $right = $this->parsePrimary();

            return ['compare', $operator, $left, $right];
        }

        return $left;
    }

    private function parsePrimary(): mixed
    {
        if ($this->match('(')) {
            $expr = $this->parse();
            $this->consume(')');

            return $expr;
        }

        if ($this->checkIdentifier() && $this->peekNext() === '(') {
            return $this->parseFunction();
        }

        if ($this->match('[')) {
            $values = [];

            while (! $this->check(']')) {
                $values[] = $this->advance();
                if ($this->check(',')) {
                    $this->advance();
                }
            }

            $this->consume(']');

            return ['array', $values];
        }

        return ['literal', $this->advance()];
    }

    private function parseFunction(): mixed
    {
        $name = $this->advance();
        $this->consume('(');

        $args = [];

        while (! $this->check(')')) {
            $args[] = $this->parseOr();
            if ($this->check(',')) {
                $this->advance();
            }
        }

        $this->consume(')');

        return ['function', $name, $args];
    }

    private function checkIdentifier(): bool
    {
        return isset($this->tokens[$this->position])
            && preg_match('/^[a-zA-Z_]/', $this->tokens[$this->position]);
    }

    private function peekNext(): ?string
    {
        return $this->tokens[$this->position + 1] ?? null;
    }

    private function match(string ...$types): bool
    {
        foreach ($types as $type) {
            if ($this->check($type)) {
                $this->advance();

                return true;
            }
        }

        return false;
    }

    private function check(string $type): bool
    {
        return isset($this->tokens[$this->position])
            && $this->tokens[$this->position] === $type;
    }

    private function advance(): mixed
    {
        return $this->tokens[$this->position++];
    }

    private function previous(): mixed
    {
        return $this->tokens[$this->position - 1];
    }

    private function consume(string $type): void
    {
        if (! $this->check($type)) {
            throw new RuntimeException("Expected {$type}");
        }
        $this->advance();
    }
}
