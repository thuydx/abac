<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Engine;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ThuyDX\ABAC\Contracts\AbacEngineInterface;
use ThuyDX\ABAC\Contracts\ConstraintRepositoryInterface;
use ThuyDX\ABAC\ValueObjects\Decision;
use ThuyDX\ABAC\ValueObjects\DecisionTrace;
use ThuyDX\ABAC\ValueObjects\TraceLevel;

final class AbacEngine implements AbacEngineInterface
{
    public function __construct(
        protected ConstraintRepositoryInterface $repository,
        protected array $policies,
    ) {}

    public function evaluate(EvaluationContext $context): bool
    {
        return $this->decide($context) === Decision::ALLOW;
    }

    public function decide(
        EvaluationContext $context,
        ?DecisionTrace $trace = null
    ): Decision {

        $traceEnabled = config('abac.trace.enabled', false);
        $traceLevel   = TraceLevel::from(
            config('abac.trace.level', 'info')
        );

        $decisionTrace = $traceEnabled
            ? ($trace ?? DecisionTrace::start(level: $traceLevel))
            : DecisionTrace::start(level: TraceLevel::NONE);

        $decision = $this->evaluateWithoutDecisionCache(
            $context,
            $decisionTrace
        );

        if ($traceEnabled && $decisionTrace->isEnabled()) {
            $this->writeTrace($context, $decision, $decisionTrace);
        }

        return $decision;
    }

    private function evaluateWithoutDecisionCache(
        EvaluationContext $context,
        DecisionTrace $decisionTrace
    ): Decision {

        $constraints = $this->repository->forUserAndPermission(
            userUuid      : $context->userUuid,
            permissionSlug: $context->permission,
            scope         : $context->scope,
            module        : $context->module,
        );

        if (empty($constraints)) {
            return Decision::DENY;
        }

        foreach ($constraints as $expression) {

            $priority = $expression['priority'] ?? 0;

            foreach ($this->policies as $policy) {

                if (! $policy->supports($expression)) {
                    continue;
                }

                $start    = microtime(true);
                $decision = $policy->evaluate($expression, $context);

                $decisionTrace->add(
                    expression : $expression,
                    policyClass: get_class($policy),
                    decision   : $decision,
                    priority   : $priority,
                    startTime  : $start
                );

                if ($decision === Decision::DENY) {
                    return Decision::DENY;
                }

                if ($decision === Decision::ALLOW) {
                    return Decision::ALLOW;
                }
            }
        }

        return Decision::DENY;
    }

    /*
    |--------------------------------------------------------------------------
    | TRACE OUTPUT
    |--------------------------------------------------------------------------
    */

    private function writeTrace(
        EvaluationContext $context,
        Decision $decision,
        DecisionTrace $trace
    ): void {

        $payload = [
            'correlation_id' => $trace->correlationId(),
            'user_uuid'      => $context->userUuid,
            'permission'     => $context->permission,
            'scope'          => $context->scope,
            'module'         => $context->module,
            'decision'       => $decision->value,
            'steps'          => $trace->all(),
            'timestamp'      => now()->toDateTimeString(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Write to Log
        |--------------------------------------------------------------------------
        */
        if (config('abac.trace.log', true)) {
            Log::channel('abac')->info('ABAC Trace', $payload);
        }

        /*
        |--------------------------------------------------------------------------
        | Write to Redis
        |--------------------------------------------------------------------------
        */
        if (config('abac.trace.redis', true)) {

            $key = sprintf(
                'abac:trace:%s',
                $trace->correlationId()
            );

            Redis::setex(
                $key,
                config('abac.trace.redis_ttl', 3600),
                json_encode($payload, JSON_THROW_ON_ERROR)
            );
        }
    }

    public function authorize(
        EvaluationContext $context,
        ?DecisionTrace $trace = null
    ): void {

        $decision = $this->decide($context, $trace);

        if ($decision !== Decision::ALLOW) {
            throw new HttpException(
                403,
                sprintf(
                    'ABAC denied: user [%s] permission [%s]',
                    $context->userUuid,
                    $context->permission
                )
            );
        }
    }
}
