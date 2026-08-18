<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Contracts;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\DTOs\AggregatedDiagnosticSummaryDTO;

interface AgentDiagnosticsFormatterInterface
{
    public function formatMarkdown(ActionableDiagnosticDTO $dto): string;

    public function formatSummaryMarkdown(AggregatedDiagnosticSummaryDTO $summary): string;
}
