<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Tests\Feature;

use AlexKassel\LaravelActionableDiagnostics\Events\DiagnosticReported;
use AlexKassel\LaravelActionableDiagnostics\Facades\Diagnostics;
use AlexKassel\LaravelActionableDiagnostics\Providers\ActionableDiagnosticsServiceProvider;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;

class DiagnosticsManagerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ActionableDiagnosticsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        config()->set('actionable-diagnostics.routes.enabled', true);
    }

    public function test_facade_records_diagnostic_and_dispatches_laravel_event(): void
    {
        Event::fake();

        Diagnostics::recoverable(
            code: 'ERR_TEST_EVENT',
            message: 'Testing facade integration',
            context: ['user_id' => 123],
            agentInstructions: 'Check test log output.'
        );

        Event::assertDispatched(DiagnosticReported::class, function (DiagnosticReported $event) {
            return $event->diagnostic->diagnosticCode === 'ERR_TEST_EVENT'
                && $event->diagnostic->message === 'Testing facade integration';
        });
    }

    public function test_rest_api_endpoint_reports_diagnostic(): void
    {
        Event::fake();

        $response = $this->postJson('/api/diagnostics/report', [
            'project_slug' => 'test-app',
            'diagnostic_code' => 'ERR_API_TEST',
            'severity' => 'RECOVERABLE',
            'message' => 'API test message',
        ]);

        $response->assertStatus(202);
        $response->assertJson(['status' => 'success']);

        Event::assertDispatched(DiagnosticReported::class);
    }
}
