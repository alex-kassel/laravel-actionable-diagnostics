<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\DTOs;

use JsonSerializable;

readonly class AggregatedDiagnosticSummaryDTO implements JsonSerializable
{
    public function __construct(
        public string $summaryId,
        public string $projectSlug,
        public string $diagnosticCode,
        public int $totalOccurrences,
        public string $firstSeenAt,
        public string $lastSeenAt,
        /** @var array<string, int> Top affected targets or URLs */
        public array $topAffectedTargets,
        /** @var array<string, mixed> Sample context payload */
        public array $sampleContext,
        public string $agentInstructions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'summary_id' => $this->summaryId,
            'project_slug' => $this->projectSlug,
            'diagnostic_code' => $this->diagnosticCode,
            'total_occurrences' => $this->totalOccurrences,
            'first_seen_at' => $this->firstSeenAt,
            'last_seen_at' => $this->lastSeenAt,
            'top_affected_targets' => $this->topAffectedTargets,
            'sample_context' => $this->sampleContext,
            'agent_instructions' => $this->agentInstructions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
