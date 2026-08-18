<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Events;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiagnosticReported
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ActionableDiagnosticDTO $diagnostic
    ) {}
}
