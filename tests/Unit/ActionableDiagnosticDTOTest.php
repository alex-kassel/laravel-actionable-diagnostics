<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Tests\Unit;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;
use PHPUnit\Framework\TestCase;

class ActionableDiagnosticDTOTest extends TestCase
{
    public function test_it_creates_dto_from_array(): void
    {
        $dto = ActionableDiagnosticDTO::fromArray([
            'project_slug' => 'scraper-core',
            'diagnostic_code' => 'ERR_ITEM_TIMEOUT',
            'severity' => 'RECOVERABLE',
            'message' => 'Detail page timeout',
            'remediation_steps' => ['Retry request'],
            'agent_instructions' => 'Increase timeout setting in config.',
        ]);

        $this->assertEquals('scraper-core', $dto->projectSlug);
        $this->assertEquals('ERR_ITEM_TIMEOUT', $dto->diagnosticCode);
        $this->assertEquals(ErrorSeverityEnum::RECOVERABLE, $dto->severity);
        $this->assertEquals('Detail page timeout', $dto->message);
        $this->assertCount(1, $dto->remediationSteps);
    }
}
