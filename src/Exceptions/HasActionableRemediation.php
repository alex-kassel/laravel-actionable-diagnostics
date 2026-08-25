<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Exceptions;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;

trait HasActionableRemediation
{
    /** @var array<int, string> */
    protected array $remediationSteps = [];

    protected string $agentInstructions = '';

    protected string $diagnosticCode = 'ERR_CUSTOM_ACTIONABLE_EXCEPTION';

    protected ErrorSeverityEnum $severity = ErrorSeverityEnum::RECOVERABLE;

    /** @var array<string, mixed> */
    protected array $diagnosticContext = [];

    public function getDiagnosticCode(): string
    {
        return $this->diagnosticCode;
    }

    public function getSeverity(): ErrorSeverityEnum
    {
        return $this->severity;
    }

    /**
     * @return array<int, string>
     */
    public function getRemediationSteps(): array
    {
        return $this->remediationSteps;
    }

    public function getAgentInstructions(): string
    {
        return $this->agentInstructions;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDiagnosticContext(): array
    {
        return $this->diagnosticContext;
    }

    public function toDiagnosticDTO(): ActionableDiagnosticDTO
    {
        return ActionableDiagnosticDTO::fromArray([
            'diagnostic_code' => $this->getDiagnosticCode(),
            'severity' => $this->getSeverity(),
            'message' => $this->getMessage(),
            'remediation_steps' => $this->getRemediationSteps(),
            'agent_instructions' => $this->getAgentInstructions(),
            'context' => $this->getDiagnosticContext(),
            'stack_trace' => array_slice($this->getTrace(), 0, 10),
        ]);
    }
}
