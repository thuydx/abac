<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\ValueObjects;

enum Decision: string
{
    case ALLOW = 'allow';
    case DENY = 'deny';
    case ABSTAIN = 'abstain';

    public static function fromBoolean(bool $value): self
    {
        return $value ? self::ALLOW : self::ABSTAIN;
    }
}
