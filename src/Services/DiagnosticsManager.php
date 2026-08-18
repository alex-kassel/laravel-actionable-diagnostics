<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Services;

use AlexKassel\LaravelActionableDiagnostics\Contracts\ActionableDiagnosticInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\ActionableExceptionInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\AgentDiagnosticsFormatterInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\DiagnosticBufferInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\SensitiveDataMaskerInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\WebhookDispatcherInterface;
use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\Enums\DiagnosticSourceEnum;
use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;
use AlexKassel\LaravelActionableDiagnostics\Events\DiagnosticReported;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Psr\Log\LoggerInterface;
use Throwable;

class DiagnosticsManager
{
    public function __construct(
        protected SensitiveDataMaskerInterface $masker,
        protected AgentDiagnosticsFormatterInterface $formatter,
        protected WebhookDispatcherInterface $webhookDispatcher,
        protected DiagnosticBufferInterface $buffer,
        protected ?EventDispatcher $events = null,
        protected ?LoggerInterface $logger = null,
        protected string $projectSlug = 'default-app',
        protected string $environment = 'production',
        protected bool $bufferEnabled = true,
        protected int $bufferMaxItems = 100
    ) {}

    public function fatal(Throwable $exception): void
    {
        if ($exception instanceof ActionableExceptionInterface) {
            $dto = ActionableDiagnosticDTO::fromArray([
                'project_slug' => $this->projectSlug,
                'environment' => $this->environment,
                'diagnostic_code' => $exception->getDiagnosticCode(),
                'severity' => ErrorSeverityEnum::FATAL,
                'message' => $exception->getMessage(),
                'remediation_steps' => $exception->getRemediationSteps(),
                'agent_instructions' => $exception->getAgentInstructions(),
                'context' => $exception->getDiagnosticContext(),
                'stack_trace' => array_slice($exception->getTrace(), 0, 10),
            ]);
        } else {
            $dto = ActionableDiagnosticDTO::fromArray([
                'project_slug' => $this->projectSlug,
                'environment' => $this->environment,
                'diagnostic_code' => 'ERR_UNHANDLED_EXCEPTION',
                'severity' => ErrorSeverityEnum::FATAL,
                'message' => $exception->getMessage(),
                'remediation_steps' => ['Inspect exception stack trace', 'Verify system logs'],
                'agent_instructions' => 'Analyze exception message and stack trace frame to isolate root cause.',
                'context' => ['exception_class' => get_class($exception)],
                'stack_trace' => array_slice($exception->getTrace(), 0, 10),
            ]);
        }

        $this->processDiagnostic($dto, true);
    }

    public function recoverable(
        string $code,
        string $message,
        array $context = [],
        array $remediationSteps = [],
        string $agentInstructions = ''
    ): void {
        $dto = ActionableDiagnosticDTO::fromArray([
            'project_slug' => $this->projectSlug,
            'environment' => $this->environment,
            'diagnostic_code' => $code,
            'severity' => ErrorSeverityEnum::RECOVERABLE,
            'message' => $message,
            'remediation_steps' => $remediationSteps,
            'agent_instructions' => $agentInstructions,
            'context' => $context,
        ]);

        $this->processDiagnostic($dto, false);
    }

    public function operational(string $code, string $message, array $context = []): void
    {
        $dto = ActionableDiagnosticDTO::fromArray([
            'project_slug' => $this->projectSlug,
            'environment' => $this->environment,
            'diagnostic_code' => $code,
            'severity' => ErrorSeverityEnum::OPERATIONAL,
            'message' => $message,
            'context' => $context,
        ]);

        $this->processDiagnostic($dto, false);
    }

    public function record(ActionableDiagnosticInterface|ActionableDiagnosticDTO $diagnostic): void
    {
        $dto = $diagnostic instanceof ActionableDiagnosticInterface
            ? $diagnostic->toDiagnosticDTO()
            : $diagnostic;

        $bypassBuffer = ($dto->severity === ErrorSeverityEnum::FATAL);
        $this->processDiagnostic($dto, $bypassBuffer);
    }

    public function flushBuffer(): void
    {
        $summaries = $this->buffer->flush();

        foreach ($summaries as $summary) {
            $markdown = $this->formatter->formatSummaryMarkdown($summary);
            if ($this->logger) {
                $this->logger->info("[DIAGNOSTIC_SUMMARY] {$summary->diagnosticCode}", ['summary' => $summary->toArray()]);
            }
            $this->webhookDispatcher->dispatchSummary($summary);
        }
    }

    protected function processDiagnostic(ActionableDiagnosticDTO $dto, bool $bypassBuffer): void
    {
        $sanitizedContext = $this->masker->mask($dto->context);
        $sanitizedDTO = ActionableDiagnosticDTO::fromArray(array_merge($dto->toArray(), ['context' => $sanitizedContext]));

        if (! $bypassBuffer && $this->bufferEnabled && $sanitizedDTO->severity === ErrorSeverityEnum::OPERATIONAL) {
            $this->buffer->push($sanitizedDTO);
            if ($this->buffer->count() >= $this->bufferMaxItems) {
                $this->flushBuffer();
            }
            return;
        }

        // Fatal or immediate escalation
        if ($bypassBuffer && $this->buffer->count() > 0) {
            $this->flushBuffer();
        }

        $markdown = $this->formatter->formatMarkdown($sanitizedDTO);

        if ($this->logger) {
            $logLevel = match ($sanitizedDTO->severity) {
                ErrorSeverityEnum::FATAL => 'error',
                ErrorSeverityEnum::RECOVERABLE => 'warning',
                ErrorSeverityEnum::OPERATIONAL => 'info',
            };
            $this->logger->log($logLevel, $markdown, $sanitizedDTO->toArray());
        }

        if ($this->events) {
            $this->events->dispatch(new DiagnosticReported($sanitizedDTO));
        }

        $this->webhookDispatcher->dispatchDiagnostic($sanitizedDTO);
    }
}
