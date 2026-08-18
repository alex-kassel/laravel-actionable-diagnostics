<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Contracts;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\DTOs\AggregatedDiagnosticSummaryDTO;

interface DiagnosticBufferInterface
{
    public function push(ActionableDiagnosticDTO $dto): void;

    /**
     * @return array<int, AggregatedDiagnosticSummaryDTO>
     */
    public function flush(): array;

    public function clear(): void;

    public function count(): int;
}
