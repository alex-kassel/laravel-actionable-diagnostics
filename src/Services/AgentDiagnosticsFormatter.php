<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Services;

use AlexKassel\LaravelActionableDiagnostics\Contracts\AgentDiagnosticsFormatterInterface;
use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\DTOs\AggregatedDiagnosticSummaryDTO;
use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;

class AgentDiagnosticsFormatter implements AgentDiagnosticsFormatterInterface
{
    public function formatMarkdown(ActionableDiagnosticDTO $dto): string
    {
        $alertType = match ($dto->severity) {
            ErrorSeverityEnum::FATAL => 'CAUTION',
            ErrorSeverityEnum::RECOVERABLE => 'WARNING',
            ErrorSeverityEnum::OPERATIONAL => 'NOTE',
        };

        $lines = [];
        $lines[] = "> [!{$alertType}]";
        $lines[] = "> **[{$dto->diagnosticCode}] Diagnostic Event**";
        $lines[] = '>';
        $lines[] = "> **Message:** {$dto->message}";
        $lines[] = "> **Severity:** {$dto->severity->value} | **Project:** {$dto->projectSlug} | **Env:** {$dto->environment}";

        if (! empty($dto->remediationSteps)) {
            $lines[] = '>';
            $lines[] = '> **Human Remediation:**';
            foreach ($dto->remediationSteps as $index => $step) {
                $num = $index + 1;
                $lines[] = "> {$num}. {$step}";
            }
        }

        if (! empty($dto->agentInstructions)) {
            $lines[] = '>';
            $lines[] = '> **AI Agent Resolution Protocol:**';
            $lines[] = "> {$dto->agentInstructions}";
        }

        return implode("\n", $lines);
    }

    public function formatSummaryMarkdown(AggregatedDiagnosticSummaryDTO $summary): string
    {
        $lines = [];
        $lines[] = '> [!NOTE]';
        $lines[] = "> **[{$summary->diagnosticCode}] Aggregated Diagnostic Summary**";
        $lines[] = '>';
        $lines[] = "> **Total Occurrences:** {$summary->totalOccurrences} events";
        $lines[] = "> **Project:** {$summary->projectSlug} | **Window:** {$summary->firstSeenAt} — {$summary->lastSeenAt}";

        if (! empty($summary->topAffectedTargets)) {
            $lines[] = '>';
            $lines[] = '> **Top Affected Targets:**';
            foreach ($summary->topAffectedTargets as $target => $count) {
                $lines[] = "> * `{$target}`: {$count} times";
            }
        }

        if (! empty($summary->agentInstructions)) {
            $lines[] = '>';
            $lines[] = '> **AI Agent Protocol:**';
            $lines[] = "> {$summary->agentInstructions}";
        }

        return implode("\n", $lines);
    }
}
