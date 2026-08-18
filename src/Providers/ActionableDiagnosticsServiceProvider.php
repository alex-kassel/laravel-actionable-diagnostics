<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Providers;

use AlexKassel\LaravelActionableDiagnostics\Contracts\AgentDiagnosticsFormatterInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\DiagnosticBufferInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\SensitiveDataMaskerInterface;
use AlexKassel\LaravelActionableDiagnostics\Contracts\WebhookDispatcherInterface;
use AlexKassel\LaravelActionableDiagnostics\Http\Controllers\DiagnosticReportController;
use AlexKassel\LaravelActionableDiagnostics\Http\Middleware\VerifyDiagnosticApiKey;
use AlexKassel\LaravelActionableDiagnostics\Services\AgentDiagnosticsFormatter;
use AlexKassel\LaravelActionableDiagnostics\Services\DiagnosticBufferEngine;
use AlexKassel\LaravelActionableDiagnostics\Services\DiagnosticsManager;
use AlexKassel\LaravelActionableDiagnostics\Services\SensitiveDataMasker;
use AlexKassel\LaravelActionableDiagnostics\Services\WebhookDispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ActionableDiagnosticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/actionable-diagnostics.php',
            'actionable-diagnostics'
        );

        $this->app->singleton(SensitiveDataMaskerInterface::class, function (Application $app) {
            return new SensitiveDataMasker(
                $app['config']->get('actionable-diagnostics.masking.keys', []),
                $app['config']->get('actionable-diagnostics.masking.redaction_text', '***REDACTED***')
            );
        });

        $this->app->singleton(AgentDiagnosticsFormatterInterface::class, AgentDiagnosticsFormatter::class);

        $this->app->singleton(WebhookDispatcherInterface::class, function (Application $app) {
            return new WebhookDispatcher(
                $app['config']->get('actionable-diagnostics.webhooks.urls', []),
                $app['config']->get('actionable-diagnostics.webhooks.timeout', 5),
                $app['config']->get('actionable-diagnostics.webhooks.enabled', false)
            );
        });

        $this->app->singleton(DiagnosticBufferInterface::class, function (Application $app) {
            return new DiagnosticBufferEngine(
                $app['config']->get('actionable-diagnostics.buffer.max_items', 100),
                $app['config']->get('actionable-diagnostics.buffer.max_lifetime_seconds', 300)
            );
        });

        $this->app->singleton('laravel-actionable-diagnostics', function (Application $app) {
            return new DiagnosticsManager(
                masker: $app->make(SensitiveDataMaskerInterface::class),
                formatter: $app->make(AgentDiagnosticsFormatterInterface::class),
                webhookDispatcher: $app->make(WebhookDispatcherInterface::class),
                buffer: $app->make(DiagnosticBufferInterface::class),
                events: $app->bound('events') ? $app->make('events') : null,
                logger: $app->bound('log') ? $app->make('log') : null,
                projectSlug: (string) $app['config']->get('actionable-diagnostics.project_slug', 'default-app'),
                environment: (string) $app['config']->get('actionable-diagnostics.environment', 'production'),
                bufferEnabled: (bool) $app['config']->get('actionable-diagnostics.buffer.enabled', true),
                bufferMaxItems: (int) $app['config']->get('actionable-diagnostics.buffer.max_items', 100)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/actionable-diagnostics.php' => $this->app->configPath('actionable-diagnostics.php'),
            ], 'actionable-diagnostics-config');
        }

        $this->registerRoutes();

        $this->app->terminating(function () {
            if ($this->app->bound('laravel-actionable-diagnostics')) {
                $this->app->make('laravel-actionable-diagnostics')->flushBuffer();
            }
        });
    }

    protected function registerRoutes(): void
    {
        Route::prefix('api/diagnostics')
            ->middleware(VerifyDiagnosticApiKey::class)
            ->group(function () {
                Route::post('report', [DiagnosticReportController::class, 'report']);
                Route::post('buffer/flush', [DiagnosticReportController::class, 'flush']);
            });
    }
}
