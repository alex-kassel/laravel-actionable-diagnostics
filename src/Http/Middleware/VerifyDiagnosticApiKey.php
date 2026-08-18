<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDiagnosticApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('actionable-diagnostics.api_key');

        if (empty($configuredKey)) {
            return $next($request);
        }

        $providedKey = $request->header('X-Diagnostic-Api-Key') ?? $request->bearerToken();

        if (empty($providedKey) || ! hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized diagnostic API token',
            ], 401);
        }

        return $next($request);
    }
}
