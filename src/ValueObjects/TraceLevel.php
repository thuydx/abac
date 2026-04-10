<?php

declare(strict_types=1);

namespace ThuyDX\ABAC\ValueObjects;

enum TraceLevel: string
{
    case NONE = 'none';
    case INFO = 'info';
    case DEBUG = 'debug';
}
