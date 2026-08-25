<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Tests\Unit;

use AlexKassel\LaravelActionableDiagnostics\Contracts\ActionableDiagnosticInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\ActionableExceptionInterface;
use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;
use AlexKassel\LaravelActionableDiagnostics\Exceptions\BaseActionableException;
use AlexKassel\LaravelActionableDiagnostics\Exceptions\HasActionableRemediation;
use Exception;
use PHPUnit\Framework\TestCase;

final class ActionableExceptionTest extends TestCase
{
    public function test_base_actionable_exception_provides_dto_and_context(): void
    {
        $exception = new class(message: 'Database connection dropped', code: 500, previous: null, context: ['host' => '127.0.0.1'], remediationSteps: ['Check database credentials', 'Restart PostgreSQL service'], agentInstructions: 'Check .env file.') extends BaseActionableException {};

        $this->assertSame('ERR_ACTIONABLE_EXCEPTION', $exception->getDiagnosticCode());
        $this->assertSame(ErrorSeverityEnum::FATAL, $exception->getSeverity());
        $this->assertSame(['host' => '127.0.0.1'], $exception->getDiagnosticContext());
        $this->assertCount(2, $exception->getRemediationSteps());
        $this->assertSame('Check .env file.', $exception->getAgentInstructions());

        $dto = $exception->toDiagnosticDTO();
        $this->assertSame('Database connection dropped', $dto->message);
        $this->assertSame('ERR_ACTIONABLE_EXCEPTION', $dto->diagnosticCode);
    }

    public function test_trait_has_actionable_remediation_provides_dto(): void
    {
        $customException = new class('Custom error') extends Exception implements ActionableDiagnosticInterface, ActionableExceptionInterface
        {
            use HasActionableRemediation;
        };

        $this->assertSame('ERR_CUSTOM_ACTIONABLE_EXCEPTION', $customException->getDiagnosticCode());
        $this->assertSame(ErrorSeverityEnum::RECOVERABLE, $customException->getSeverity());
        $this->assertSame([], $customException->getDiagnosticContext());
        $this->assertSame([], $customException->getRemediationSteps());

        $dto = $customException->toDiagnosticDTO();
        $this->assertSame('Custom error', $dto->message);
    }
}
