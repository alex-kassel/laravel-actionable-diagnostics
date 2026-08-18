<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Services;

use AlexKassel\LaravelActionableDiagnostics\Contracts\WebhookDispatcherInterface;
use AlexKassel\LaravelActionableDiagnostics\DTOs\ActionableDiagnosticDTO;
use AlexKassel\LaravelActionableDiagnostics\DTOs\AggregatedDiagnosticSummaryDTO;
use Symfony\Component\HttpClient\HttpClient;
use Throwable;

class WebhookDispatcher implements WebhookDispatcherInterface
{
    /** @var array<int, string> */
    protected array $urls;
    protected int $timeout;
    protected bool $enabled;

    public function __construct(array $urls = [], int $timeout = 5, bool $enabled = true)
    {
        $this->urls = array_values(array_filter($urls));
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

    private function postPayload(array $payload): bool
    {
        $client = HttpClient::create(['timeout' => $this->timeout]);
        $success = true;

        foreach ($this->urls as $url) {
            try {
                $response = $client->request('POST', $url, [
                    'json' => $payload,
                    'headers' => ['User-Agent' => 'AlexKassel-LaravelActionableDiagnostics/1.0'],
                ]);
                if ($response->getStatusCode() >= 400) {
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
