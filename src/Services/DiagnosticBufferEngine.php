<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Services;

use AlexKassel\LaravelActionableDiagnostics\Contracts\DiagnosticBufferInterface;
use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\DTOs\AggregatedDiagnosticSummaryDTO;

class DiagnosticBufferEngine implements DiagnosticBufferInterface
{
    /** @var array<string, array<int, ActionableDiagnosticDTO>> */
    protected array $buffer = [];

    protected int $maxItems;

    protected int $maxLifetimeSeconds;

    public function __construct(int $maxItems = 100, int $maxLifetimeSeconds = 300)
    {
        $this->maxItems = $maxItems;
        $this->maxLifetimeSeconds = $maxLifetimeSeconds;
    }

    public function push(ActionableDiagnosticDTO $dto): void
    {
        $code = $dto->diagnosticCode;
        if (! isset($this->buffer[$code])) {
            $this->buffer[$code] = [];
        }

        $this->buffer[$code][] = $dto;
    }

    public function flush(): array
    {
        $summaries = [];

        foreach ($this->buffer as $code => $dtos) {
            if (empty($dtos)) {
                continue;
            }

            $first = $dtos[0];
            $last = end($dtos);
            $total = count($dtos);

            $targetCounts = [];
            $agentInstructions = $first->agentInstructions;
            $sampleContext = $first->context;
            $projectSlug = $first->projectSlug;

            foreach ($dtos as $item) {
                $target = $item->context['url'] ?? $item->context['external_id'] ?? $item->context['target'] ?? null;
                if ($target !== null && is_scalar($target)) {
                    $targetKey = (string) $target;
                    $targetCounts[$targetKey] = ($targetCounts[$targetKey] ?? 0) + 1;
                }
            }

            arsort($targetCounts);
            $topTargets = array_slice($targetCounts, 0, 5, true);

            $summaries[] = new AggregatedDiagnosticSummaryDTO(
                summaryId: sprintf('summary-%s-%s', $code, date('YmdHis')),
                projectSlug: $projectSlug,
                diagnosticCode: $code,
                totalOccurrences: $total,
                firstSeenAt: $first->timestamp,
                lastSeenAt: $last->timestamp,
                topAffectedTargets: $topTargets,
                sampleContext: $sampleContext,
                agentInstructions: $agentInstructions
            );
        }

        $this->clear();

        return $summaries;
    }

    public function clear(): void
    {
        $this->buffer = [];
    }

    public function count(): int
    {
        $total = 0;
        foreach ($this->buffer as $dtos) {
            $total += count($dtos);
        }

        return $total;
    }
}
