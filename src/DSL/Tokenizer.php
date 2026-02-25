<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\DSL;

final class Tokenizer
{
    private string $input;
    private int $position = 0;
    private int $length;

    public function __construct(string $input)
    {
        $this->input  = trim($input);
        $this->length = strlen($this->input);
    }

    public function tokenize(): array
    {
        $tokens = [];

        while ($this->position < $this->length) {

            $char = $this->input[$this->position];

            if (ctype_space($char)) {
                $this->position++;
                continue;
            }

            if (in_array($char, ['(', ')', '[', ']', ',', '!'])) {
                $tokens[] = $char;
                $this->position++;
                continue;
            }

            if ($char === '\'' || $char === '"') {
                $tokens[] = $this->readString($char);
                continue;
            }

            if (preg_match('/[a-zA-Z_]/', $char)) {
                $tokens[] = $this->readIdentifier();
                continue;
            }

            if (preg_match('/[0-9]/', $char)) {
                $tokens[] = $this->readNumber();
                continue;
            }

            $tokens[] = $this->readOperator();
        }

        return $tokens;
    }

    private function readString(string $quote): string
    {
        $this->position++;
        $start = $this->position;

        while ($this->position < $this->length
            && $this->input[$this->position] !== $quote) {
            $this->position++;
        }

        $value = substr($this->input, $start, $this->position - $start);
        $this->position++;

        return $value;
    }

    private function readIdentifier(): string
    {
        $start = $this->position;

        while ($this->position < $this->length
            && preg_match('/[a-zA-Z0-9_]/', $this->input[$this->position])) {
            $this->position++;
        }

        return substr($this->input, $start, $this->position - $start);
    }

    private function readNumber(): string
    {
        $start = $this->position;

        while ($this->position < $this->length
            && preg_match('/[0-9.]/', $this->input[$this->position])) {
            $this->position++;
        }

        return substr($this->input, $start, $this->position - $start);
    }

    private function readOperator(): string
    {
        $operators = ['==', '!=', '>=', '<=', '&&', '||', '>', '<'];

        foreach ($operators as $op) {
            if (str_starts_with(substr($this->input, $this->position), $op)) {
                $this->position += strlen($op);
                return $op;
            }
        }

        if (str_starts_with(substr($this->input, $this->position), 'in')) {
            $this->position += 2;
            return 'in';
        }

        throw new \RuntimeException('Invalid operator');
    }
}
