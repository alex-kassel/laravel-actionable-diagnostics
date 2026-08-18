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
        public ?array $stackTrace = null,
        public string $fingerprint = '',
        public DiagnosticSourceEnum $source = DiagnosticSourceEnum::LOCAL_IN_PROCESS,
        public string $timestamp = '',
    ) {}

    public static function fromArray(array $data): self
    {
        $severity = is_string($data['severity'] ?? null)
            ? ErrorSeverityEnum::from(strtoupper($data['severity']))
            : ($data['severity'] ?? ErrorSeverityEnum::RECOVERABLE);

        $source = is_string($data['source'] ?? null)
            ? DiagnosticSourceEnum::from(strtoupper($data['source']))
            : ($data['source'] ?? DiagnosticSourceEnum::LOCAL_IN_PROCESS);

        return new self(
            id: (string) ($data['id'] ?? self::generateUuid()),
            projectSlug: (string) ($data['project_slug'] ?? 'default-app'),
            environment: (string) ($data['environment'] ?? 'production'),
            diagnosticCode: (string) ($data['diagnostic_code'] ?? 'ERR_DIAGNOSTIC_UNSPECIFIED'),
            severity: $severity,
            message: (string) ($data['message'] ?? 'Operational diagnostic event recorded'),
            remediationSteps: (array) ($data['remediation_steps'] ?? []),
            agentInstructions: (string) ($data['agent_instructions'] ?? ''),
            context: (array) ($data['context'] ?? []),
            stackTrace: $data['stack_trace'] ?? null,
            fingerprint: (string) ($data['fingerprint'] ?? self::computeFingerprint($data['diagnostic_code'] ?? '', $data['message'] ?? '')),
            source: $source,
            timestamp: (string) ($data['timestamp'] ?? date('c')),
        );
    }

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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private static function computeFingerprint(string $code, string $message): string
    {
        return md5($code . '|' . $message);
    }
}
