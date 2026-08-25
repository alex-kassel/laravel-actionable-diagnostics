<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Http\Controllers;

use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\Enums\DiagnosticSourceEnum;
use AlexKassel\LaravelActionableDiagnostics\Services\DiagnosticsManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DiagnosticReportController extends Controller
{
    public function report(Request $request, DiagnosticsManager $manager): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->validate([
            'project_slug' => 'required|string',
            'diagnostic_code' => 'required|string',
            'severity' => 'required|string',
            'message' => 'required|string',
            'remediation_steps' => 'nullable|array',
            'agent_instructions' => 'nullable|string',
            'context' => 'nullable|array',
            'stack_trace' => 'nullable|array',
        ]);

        $payload['source'] = DiagnosticSourceEnum::REMOTE_REST_API->value;
        $dto = ActionableDiagnosticDTO::fromArray($payload);

        $manager->record($dto);

        return response()->json([
            'status' => 'success',
            'message' => 'Diagnostic report received',
            'id' => $dto->id,
        ], 202);
    }

    public function flush(DiagnosticsManager $manager): JsonResponse
    {
        $manager->flushBuffer();

        return response()->json([
            'status' => 'success',
            'message' => 'Diagnostic buffer flushed',
        ]);
    }
}
