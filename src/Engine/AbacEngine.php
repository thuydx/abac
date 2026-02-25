<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\Engine;

use ThuyDX\ABAC\Contracts\AbacEngineInterface;
use ThuyDX\ABAC\Contracts\ConstraintRepositoryInterface;
use ThuyDX\ABAC\Contracts\PolicyInterface;
use ThuyDX\ABAC\ValueObjects\Decision;

final class AbacEngine implements AbacEngineInterface
{
    /**
     * @param PolicyInterface[] $policies
     */
    public function __construct(
        protected ConstraintRepositoryInterface $repository,
        protected array $policies,
    ) {}

    public function evaluate(EvaluationContext $context): bool
    {
        return $this->decide($context) === Decision::ALLOW;
    }

    public function decide(EvaluationContext $context): Decision
    {
        $constraints = $this->repository->forUserAndPermission(
            userUuid: $context->userUuid,
            permissionSlug: $context->permission,
            scope: $context->scope,
            module: $context->module,
        );

        if (empty($constraints)) {
            return Decision::DENY;
        }

        // Priority DESC (highest first)
        usort($constraints, fn ($a, $b) =>
            ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0)
        );

        foreach ($constraints as $expression) {

            foreach ($this->policies as $policy) {

                if (! $policy->supports($expression)) {
                    continue;
                }

                $decision = $policy->evaluate($expression, $context);

                if ($decision === Decision::DENY) {
                    return Decision::DENY; // deny override
                }

                if ($decision === Decision::ALLOW) {
                    return Decision::ALLOW;
                }

                // ABSTAIN → continue
            }
        }

        return Decision::DENY;
    }
}
