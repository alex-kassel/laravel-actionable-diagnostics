<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Contracts;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\DTOs\AggregatedDiagnosticSummaryDTO;

interface WebhookDispatcherInterface
{
    public function dispatchDiagnostic(ActionableDiagnosticDTO $dto): bool;

    public function dispatchSummary(AggregatedDiagnosticSummaryDTO $summary): bool;
}
