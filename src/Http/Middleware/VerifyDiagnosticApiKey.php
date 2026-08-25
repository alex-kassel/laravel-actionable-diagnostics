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

        if (! is_string($configuredKey) || $configuredKey === '') {
            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        $header = $request->header('X-Diagnostic-Api-Key');
        $firstHeader = is_array($header) ? reset($header) : $header;
        $headerKey = is_string($firstHeader) ? $firstHeader : '';

        $bearerKey = $request->bearerToken();
        $providedKey = $headerKey !== '' ? $headerKey : (is_string($bearerKey) ? $bearerKey : '');

        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized diagnostic API token',
            ], 401);
        }

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
