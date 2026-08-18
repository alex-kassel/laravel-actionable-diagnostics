<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Enums;

enum BufferDriverEnum: string
{
    case ARRAY = 'array';
    case CACHE = 'cache';
}
