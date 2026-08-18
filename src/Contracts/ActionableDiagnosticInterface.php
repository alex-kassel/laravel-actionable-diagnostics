<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Contracts;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;

interface ActionableDiagnosticInterface
{
    public function toDiagnosticDTO(): ActionableDiagnosticDTO;
}
