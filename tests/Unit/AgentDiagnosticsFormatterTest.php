<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Tests\Unit;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\Services\AgentDiagnosticsFormatter;
use PHPUnit\Framework\TestCase;

class AgentDiagnosticsFormatterTest extends TestCase
{
    public function test_it_formats_markdown_alert(): void
    {
        $formatter = new AgentDiagnosticsFormatter();
        $dto = ActionableDiagnosticDTO::fromArray([
            'diagnostic_code' => 'ERR_CONFIG',
            'severity' => 'FATAL',
            'message' => 'Invalid configuration',
            'remediation_steps' => ['Check config file'],
            'agent_instructions' => 'Run php artisan config:clear.',
        ]);

        $markdown = $formatter->formatMarkdown($dto);

        $this->assertStringContainsString('> [!CAUTION]', $markdown);
        $this->assertStringContainsString('ERR_CONFIG', $markdown);
        $this->assertStringContainsString('Run php artisan config:clear.', $markdown);
    }
}
