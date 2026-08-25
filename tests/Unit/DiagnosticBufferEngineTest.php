<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Tests\Unit;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\Services\DiagnosticBufferEngine;
use PHPUnit\Framework\TestCase;

class DiagnosticBufferEngineTest extends TestCase
{
    public function test_it_buffers_and_flushes_aggregated_summary(): void
    {
        $buffer = new DiagnosticBufferEngine;

        $dto1 = ActionableDiagnosticDTO::fromArray([
            'diagnostic_code' => 'WARN_TIMEOUT',
            'context' => ['url' => 'https://example.com/item1'],
        ]);
        $dto2 = ActionableDiagnosticDTO::fromArray([
            'diagnostic_code' => 'WARN_TIMEOUT',
            'context' => ['url' => 'https://example.com/item1'],
        ]);

        $buffer->push($dto1);
        $buffer->push($dto2);

        $this->assertEquals(2, $buffer->count());

        $summaries = $buffer->flush();

        $this->assertCount(1, $summaries);
        $this->assertEquals('WARN_TIMEOUT', $summaries[0]->diagnosticCode);
        $this->assertEquals(2, $summaries[0]->totalOccurrences);
        $this->assertEquals(0, $buffer->count());
    }
}
