<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\DTOs;

use AlexKassel\LaravelActionableDiagnostics\Enums\DiagnosticSourceEnum;
use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;
use JsonSerializable;

readonly class ActionableDiagnosticDTO implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $projectSlug,
        public string $environment,
        public string $diagnosticCode,
        public ErrorSeverityEnum $severity,
        public string $message,
        /** @var array<int, string> */
        public array $remediationSteps = [],
        public string $agentInstructions = '',
        /** @var array<string, mixed> */
        public array $context = [],
        /** @var array<int, mixed>|null */
        public ?array $stackTrace = null,
        public string $fingerprint = '',
        public DiagnosticSourceEnum $source = DiagnosticSourceEnum::LOCAL_IN_PROCESS,
        public string $timestamp = '',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rawSeverity = $data['severity'] ?? null;
        if ($rawSeverity instanceof ErrorSeverityEnum) {
            $severity = $rawSeverity;
        } elseif (is_string($rawSeverity)) {
            $severity = ErrorSeverityEnum::tryFrom(strtoupper($rawSeverity)) ?? ErrorSeverityEnum::RECOVERABLE;
        } else {
            $severity = ErrorSeverityEnum::RECOVERABLE;
        }

        $rawSource = $data['source'] ?? null;
        if ($rawSource instanceof DiagnosticSourceEnum) {
            $source = $rawSource;
        } elseif (is_string($rawSource)) {
            $source = DiagnosticSourceEnum::tryFrom(strtoupper($rawSource)) ?? DiagnosticSourceEnum::LOCAL_IN_PROCESS;
        } else {
            $source = DiagnosticSourceEnum::LOCAL_IN_PROCESS;
        }

        /** @var array<int, string> $remediationSteps */
        $remediationSteps = [];
        if (is_array($data['remediation_steps'] ?? null)) {
            foreach ($data['remediation_steps'] as $step) {
                if (is_string($step) || is_numeric($step)) {
                    $remediationSteps[] = (string) $step;
                }
            }
        }

        /** @var array<string, mixed> $context */
        $context = is_array($data['context'] ?? null) ? $data['context'] : [];

        /** @var array<int, mixed>|null $stackTrace */
        $stackTrace = is_array($data['stack_trace'] ?? null) ? $data['stack_trace'] : null;

        $code = is_string($data['diagnostic_code'] ?? null) ? $data['diagnostic_code'] : 'ERR_DIAGNOSTIC_UNSPECIFIED';
        $message = is_string($data['message'] ?? null) ? $data['message'] : 'Operational diagnostic event recorded';
        $fingerprint = is_string($data['fingerprint'] ?? null) ? $data['fingerprint'] : self::computeFingerprint($code, $message);

        return new self(
            id: is_string($data['id'] ?? null) ? $data['id'] : self::generateUuid(),
            projectSlug: is_string($data['project_slug'] ?? null) ? $data['project_slug'] : 'default-app',
            environment: is_string($data['environment'] ?? null) ? $data['environment'] : 'production',
            diagnosticCode: $code,
            severity: $severity,
            message: $message,
            remediationSteps: $remediationSteps,
            agentInstructions: is_string($data['agent_instructions'] ?? null) ? $data['agent_instructions'] : '',
            context: $context,
            stackTrace: $stackTrace,
            fingerprint: $fingerprint,
            source: $source,
            timestamp: is_string($data['timestamp'] ?? null) ? $data['timestamp'] : date('c'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_slug' => $this->projectSlug,
            'environment' => $this->environment,
            'diagnostic_code' => $this->diagnosticCode,
            'severity' => $this->severity->value,
            'message' => $this->message,
            'remediation_steps' => $this->remediationSteps,
            'agent_instructions' => $this->agentInstructions,
            'context' => $this->context,
            'stack_trace' => $this->stackTrace,
            'fingerprint' => $this->fingerprint,
            'source' => $this->source->value,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
        );
    }

    private static function computeFingerprint(string $code, string $message): string
    {
        return md5($code.'|'.$message);
    }
}
