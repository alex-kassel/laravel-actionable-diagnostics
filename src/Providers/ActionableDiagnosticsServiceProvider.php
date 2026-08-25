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
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class ActionableDiagnosticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/actionable-diagnostics.php',
            'actionable-diagnostics'
        );

        $this->app->singleton(SensitiveDataMaskerInterface::class, function () {
            $rawKeys = config('actionable-diagnostics.masking.keys');
            /** @var array<int, string> $keys */
            $keys = is_array($rawKeys) ? $rawKeys : [];
            $rawText = config('actionable-diagnostics.masking.redaction_text');
            $redactionText = is_string($rawText) ? $rawText : '***REDACTED***';

            return new SensitiveDataMasker($keys, $redactionText);
        });

        $this->app->singleton(AgentDiagnosticsFormatterInterface::class, AgentDiagnosticsFormatter::class);

        $this->app->singleton(WebhookDispatcherInterface::class, function () {
            $rawUrls = config('actionable-diagnostics.webhooks.urls');
            /** @var array<int, string> $urls */
            $urls = is_array($rawUrls) ? $rawUrls : [];
            $rawTimeout = config('actionable-diagnostics.webhooks.timeout');
            $timeout = is_numeric($rawTimeout) ? (int) $rawTimeout : 5;
            $enabled = (bool) config('actionable-diagnostics.webhooks.enabled', false);

            return new WebhookDispatcher($urls, $timeout, $enabled);
        });

        $this->app->singleton(DiagnosticBufferInterface::class, function () {
            $rawMax = config('actionable-diagnostics.buffer.max_items');
            $maxItems = is_numeric($rawMax) ? (int) $rawMax : 100;
            $rawLifetime = config('actionable-diagnostics.buffer.max_lifetime_seconds');
            $maxLifetime = is_numeric($rawLifetime) ? (int) $rawLifetime : 300;

            return new DiagnosticBufferEngine($maxItems, $maxLifetime);
        });

        $this->app->singleton('laravel-actionable-diagnostics', function (Application $app) {
            /** @var Dispatcher|null $events */
            $events = $app->bound('events') ? $app->make('events') : null;
            /** @var LoggerInterface|null $logger */
            $logger = $app->bound('log') ? $app->make('log') : null;

            $rawSlug = config('actionable-diagnostics.project_slug');
            $projectSlug = is_string($rawSlug) ? $rawSlug : 'default-app';

            $rawEnv = config('actionable-diagnostics.environment');
            $environment = is_string($rawEnv) ? $rawEnv : 'production';

            $bufferEnabled = (bool) config('actionable-diagnostics.buffer.enabled', true);

            $rawMaxItems = config('actionable-diagnostics.buffer.max_items');
            $bufferMaxItems = is_numeric($rawMaxItems) ? (int) $rawMaxItems : 100;

            return new DiagnosticsManager(
                masker: $app->make(SensitiveDataMaskerInterface::class),
                formatter: $app->make(AgentDiagnosticsFormatterInterface::class),
                webhookDispatcher: $app->make(WebhookDispatcherInterface::class),
                buffer: $app->make(DiagnosticBufferInterface::class),
                events: $events,
                logger: $logger,
                projectSlug: $projectSlug,
                environment: $environment,
                bufferEnabled: $bufferEnabled,
                bufferMaxItems: $bufferMaxItems
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/actionable-diagnostics.php' => $this->app->configPath('actionable-diagnostics.php'),
            ], 'actionable-diagnostics-config');
        }

        if ((bool) config('actionable-diagnostics.routes.enabled', false)) {
            $this->registerRoutes();
        }

        $this->app->terminating(function () {
            if ($this->app->bound('laravel-actionable-diagnostics')) {
                $manager = $this->app->make('laravel-actionable-diagnostics');
                if ($manager instanceof DiagnosticsManager) {
                    $manager->flushBuffer();
                }
            }
        });
    }

    protected function registerRoutes(): void
    {
        $rawPrefix = config('actionable-diagnostics.routes.prefix');
        $prefix = is_string($rawPrefix) ? $rawPrefix : 'api/diagnostics';

        Route::prefix($prefix)
            ->middleware(VerifyDiagnosticApiKey::class)
            ->group(function () {
                Route::post('report', [DiagnosticReportController::class, 'report']);
                Route::post('buffer/flush', [DiagnosticReportController::class, 'flush']);
            });
    }
}
