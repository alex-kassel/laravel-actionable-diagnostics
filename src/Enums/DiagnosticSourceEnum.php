<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Enums;

enum DiagnosticSourceEnum: string
{
    case LOCAL_IN_PROCESS = 'LOCAL_IN_PROCESS';
    case REMOTE_REST_API = 'REMOTE_REST_API';
}
