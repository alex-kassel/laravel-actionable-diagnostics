<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Facades;

use AlexKassel\LaravelActionableDiagnostics\Contracts\ActionableDiagnosticInterface;
use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\Services\DiagnosticsManager;
use Illuminate\Support\Facades\Facade;
use Throwable;

/**
 * @method static void fatal(Throwable $exception)
 * @method static void recoverable(string $code, string $message, array<string, mixed> $context = [], array<int, string> $remediationSteps = [], string $agentInstructions = '')
 * @method static void operational(string $code, string $message, array<string, mixed> $context = [])
 * @method static void record(ActionableDiagnosticInterface|ActionableDiagnosticDTO $diagnostic)
 * @method static void flushBuffer()
 *
 * @see DiagnosticsManager
 */
class Diagnostics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-actionable-diagnostics';
    }
}
