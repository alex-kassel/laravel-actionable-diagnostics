<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Enums;

enum ErrorSeverityEnum: string
{
    case FATAL = 'FATAL';
    case RECOVERABLE = 'RECOVERABLE';
    case OPERATIONAL = 'OPERATIONAL';
}
