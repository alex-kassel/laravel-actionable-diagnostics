<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Services;

use AlexKassel\LaravelActionableDiagnostics\Contracts\WebhookDispatcherInterface;
use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\DTOs\AggregatedDiagnosticSummaryDTO;
use Illuminate\Support\Facades\Http;
use Throwable;

class WebhookDispatcher implements WebhookDispatcherInterface
{
    /** @var array<int, string> */
    protected array $urls;

    protected int $timeout;

    protected bool $enabled;

    /**
     * @param  array<int, string>  $urls
     */
    public function __construct(array $urls = [], int $timeout = 5, bool $enabled = true)
    {
        $this->urls = array_values(array_filter($urls, fn (string $url) => $url !== ''));
        $this->timeout = $timeout;
        $this->enabled = $enabled && ! empty($this->urls);
    }

    public function dispatchDiagnostic(ActionableDiagnosticDTO $dto): bool
    {
        if (! $this->enabled) {
            return false;
        }

        return $this->postPayload([
            'type' => 'diagnostic_event',
            'data' => $dto->toArray(),
        ]);
    }

    public function dispatchSummary(AggregatedDiagnosticSummaryDTO $summary): bool
    {
        if (! $this->enabled) {
            return false;
        }

        return $this->postPayload([
            'type' => 'diagnostic_summary',
            'data' => $summary->toArray(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postPayload(array $payload): bool
    {
        $success = true;

        foreach ($this->urls as $url) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders(['User-Agent' => 'AlexKassel-LaravelActionableDiagnostics/1.0'])
                    ->post($url, $payload);

                if (! $response->successful()) {
                    $success = false;
                }
            } catch (Throwable) {
                // Fault tolerant: failing webhooks must never throw secondary unhandled exceptions
                $success = false;
            }
        }

        return $success;
    }
}
