<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\ValueObjects;

final class DecisionTrace
{
    /**
     * @var array<int, array{
     *     timestamp: float,
     *     priority: int,
     *     policy: string,
     *     decision: string,
     *     duration_ms: float,
     *     expression: array
     * }>
     */
    private array $steps;

    private function __construct(
        private readonly string $correlationId,
        private readonly TraceLevel $level,
        array $steps = []
    ) {
        $this->steps = $steps;
    }

    public static function start(
        ?string $correlationId = null,
        TraceLevel $level = TraceLevel::INFO
    ): self {
        return new self(
            $correlationId ?? bin2hex(random_bytes(8)),
            $level
        );
    }

    public function add(
        array $expression,
        string $policyClass,
        Decision $decision,
        int $priority,
        float $startTime
    ): self {

        if ($this->level === TraceLevel::NONE) {
            return $this;
        }

        $duration = (microtime(true) - $startTime) * 1000;

        $newSteps = $this->steps;

        $newSteps[] = [
            'timestamp' => microtime(true),
            'priority' => $priority,
            'policy' => $policyClass,
            'decision' => $decision->value,
            'duration_ms' => round($duration, 3),
            'expression' => $this->level === TraceLevel::DEBUG
                ? $expression
                : [],
        ];

        return new self(
            $this->correlationId,
            $this->level,
            $newSteps
        );
    }

    public function all(): array
    {
        return $this->steps;
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }

    public function isEnabled(): bool
    {
        return $this->level !== TraceLevel::NONE;
    }
}
