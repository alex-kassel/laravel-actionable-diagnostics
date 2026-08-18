<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Contracts;

use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;

interface ActionableExceptionInterface
{
    public function getDiagnosticCode(): string;

    public function getSeverity(): ErrorSeverityEnum;

    /**
     * @return array<int, string>
     */
    public function getRemediationSteps(): array;

    public function getAgentInstructions(): string;

    /**
     * @return array<string, mixed>
     */
    public function getDiagnosticContext(): array;
}
