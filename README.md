# Laravel Actionable Diagnostics

[![Latest Stable Version](https://poser.pugx.org/alex-kassel/laravel-actionable-diagnostics/v)](https://packagist.org/packages/alex-kassel/laravel-actionable-diagnostics)
[![License](https://poser.pugx.org/alex-kassel/laravel-actionable-diagnostics/license)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue.svg)](https://php.net)

Multichannel diagnostic event engine, actionable exception taxonomy, event buffering, and AI agent remediation framework for PHP 8.2+ and Laravel applications.

---

## Key Features

* **Single-Line Developer DX:** Clean convenience facade (`Diagnostics::fatal()`, `Diagnostics::recoverable()`, `Diagnostics::operational()`, `Diagnostics::record($dto)`).
* **Actionable Error & Event Taxonomy:** Machine-readable diagnostic codes, severity levels (`FATAL`, `RECOVERABLE`, `OPERATIONAL`), human remediation steps, and AI resolution prompts.
* **Event Aggregation & Summary Flushing:** In-memory event buffering to prevent log spam, flushing aggregated summary reports on process completion or item threshold.
* **Dual Ingestion & Dispatch:** Local in-process PHP invocation AND remote REST API (`POST /api/diagnostics/report`), dispatching to Monolog PSR-3 log channels, Laravel Events, and HTTP POST Webhooks.
* **Sensitive Data Masking:** Automatic recursive redaction of credentials, tokens, and private keys across log files and webhook payloads.
* **AI Agent Friendly Markdown Output:** Formats diagnostic alerts into structured GitHub Markdown blocks (`> [!CAUTION]`) with clear resolution instructions.

---

## Installation

Install the package via Composer:

```bash
composer require alex-kassel/laravel-actionable-diagnostics
```

The service provider and `Diagnostics` facade will automatically register via Laravel package discovery.

Optionally publish the configuration file:

```bash
php artisan vendor:publish --tag="actionable-diagnostics-config"
```

---

## Usage Examples

### 1. Single-Line Developer API

```php
use AlexKassel\LaravelActionableDiagnostics\Facades\Diagnostics;

// Report a fatal exception with actionable remediation
Diagnostics::fatal($exception);

// Record a recoverable operational anomaly
Diagnostics::recoverable(
    code: 'ERR_SPIDER_ITEM_TIMEOUT',
    message: 'Item detail page timed out',
    context: ['url' => $url, 'spider' => 'spider-one'],
    remediationSteps: [
        'Check target server availability',
        'Verify proxy pool health',
    ],
    agentInstructions: 'If count > 50, check proxy pool health or increase timeout.'
);

// Record an operational event (automatically buffered)
Diagnostics::operational(
    code: 'WARN_CONTENT_SIZE_ANOMALY',
    message: 'Content size changed without fingerprint change',
    context: ['external_id' => '12345', 'delta_bytes' => 4500]
);
```

### 2. Creating Custom Actionable Exceptions

Extend `BaseActionableException` or use the `HasActionableRemediation` trait:

```php
use AlexKassel\LaravelActionableDiagnostics\Exceptions\BaseActionableException;
use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;

class DatabaseConnectionFailedException extends BaseActionableException
{
    protected string $diagnosticCode = 'ERR_DATABASE_CONNECTION_FAILED';
    protected ErrorSeverityEnum $severity = ErrorSeverityEnum::FATAL;
    protected array $remediationSteps = [
        'Verify database container status',
        'Check DB_HOST and DB_PORT in .env',
    ];
    protected string $agentInstructions = '1. Run docker ps to check DB container. 2. Verify .env credentials.';
}
```

### 3. Remote REST API Ingestion

Send diagnostic payloads from remote applications via `POST /api/diagnostics/report`:

```json
{
  "project_slug": "car-subscription-catalog",
  "diagnostic_code": "ERR_DATABASE_CONNECTION_FAILED",
  "severity": "FATAL",
  "message": "Could not connect to database host 'db.internal'",
  "remediation_steps": [
    "Verify MySQL container status",
    "Check DB_HOST in .env"
  ],
  "agent_instructions": "1. Run docker ps to check MySQL. 2. Verify .env credentials.",
  "context": { "host": "db.internal", "port": 3306 }
}
```

---

## Testing

Run the test suite via Composer:

```bash
composer test
```

Or invoke PHPUnit directly:

```bash
vendor/bin/phpunit
```

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
