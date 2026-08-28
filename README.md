<h1 align="center">🩺 Laravel Actionable Diagnostics</h1>

<p align="center">
  <strong>Multichannel diagnostic event engine, actionable exception taxonomy, event buffering, and AI agent remediation framework</strong>
</p>

<p align="center">
  <a href="#installation">Installation</a> •
  <a href="#configuration">Configuration</a> •
  <a href="#usage">Usage</a> •
  <a href="RELEASE-GATE.md">Release Gate</a> •
  <a href="CHANGELOG.md">Changelog</a>
</p>

<p align="center">
  <a href="RELEASE-GATE.md"><img src="https://img.shields.io/badge/Audit-Verified-10b981?logo=shield" alt="Audit Verified"></a>
  <a href="https://packagist.org/packages/alex-kassel/laravel-actionable-diagnostics"><img src="https://img.shields.io/packagist/v/alex-kassel/laravel-actionable-diagnostics?color=f59e0b&logo=packagist&logoColor=white" alt="Latest Version"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012%20%7C%2013-ff2d20?logo=laravel&logoColor=white" alt="Laravel Support"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.2+-777bb4?logo=php&logoColor=white" alt="PHP Support"></a>
  <a href="RELEASE-GATE.md"><img src="https://img.shields.io/badge/PHPStan-Level%20Max-8b5cf6?logo=php&logoColor=white" alt="PHPStan Level Max"></a>
</p>

---

**Laravel Actionable Diagnostics** is a multichannel diagnostic event engine and actionable exception taxonomy framework for PHP 8.2+ and Laravel applications. It bridges runtime observability with automated AI remediation by turning exceptions and log events into structured, actionable problem definitions with step-by-step resolution prompts.

---

## Key Features

* **Single-Line Developer DX:** Clean convenience facade (`Diagnostics::fatal()`, `Diagnostics::recoverable()`, `Diagnostics::operational()`, `Diagnostics::record($dto)`).
* **Actionable Error & Event Taxonomy:** Machine-readable diagnostic codes, severity levels (`FATAL`, `RECOVERABLE`, `OPERATIONAL`), human remediation steps, and AI resolution prompts.
* **Event Aggregation & Summary Flushing:** In-memory event buffering to prevent log spam, flushing aggregated summary reports on process completion or item threshold.
* **Dual Ingestion & Dispatch:** Local in-process PHP invocation and optional remote REST API (`POST /api/diagnostics/report`), dispatching to Monolog PSR-3 log channels, Laravel Events, and HTTP POST Webhooks.
* **Sensitive Data Masking:** Automatic recursive redaction of credentials, tokens, and private keys across log files and webhook payloads.
* **AI Agent Friendly Markdown Output:** Formats diagnostic alerts into structured GitHub Markdown blocks (`> [!CAUTION]`) with clear resolution instructions.

---

## Requirements

* **PHP:** 8.2+ (tested on 8.2, 8.3, 8.4, 8.5)
* **Laravel Framework:** 10.x | 11.x | 12.x | 13.x

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

## Configuration

The published `config/actionable-diagnostics.php` configuration file allows fine-tuning buffer sizes, webhook targets, API keys, and masking keys:

```php
return [
    'project_slug' => env('DIAGNOSTICS_PROJECT_SLUG', 'default-app'),
    'environment'  => env('APP_ENV', 'production'),
    'api_key'      => env('DIAGNOSTICS_API_KEY', null),

    'routes' => [
        'enabled' => env('ACTIONABLE_DIAGNOSTICS_ROUTES_ENABLED', false),
        'prefix'  => env('ACTIONABLE_DIAGNOSTICS_ROUTES_PREFIX', 'api/diagnostics'),
    ],

    'buffer' => [
        'enabled'              => env('DIAGNOSTICS_BUFFER_ENABLED', true),
        'max_items'            => (int) env('DIAGNOSTICS_BUFFER_MAX_ITEMS', 100),
        'max_lifetime_seconds' => (int) env('DIAGNOSTICS_BUFFER_MAX_LIFETIME', 300),
    ],

    'masking' => [
        'enabled'        => true,
        'redaction_text' => '***REDACTED***',
        'keys'           => [
            'password', 'pass', 'secret', 'bearer', 'token',
            'api_key', 'authorization', 'credit_card', 'ssn',
            'private_key', 'cookie', 'db_password',
        ],
    ],

    'webhooks' => [
        'enabled' => env('DIAGNOSTICS_WEBHOOK_ENABLED', false),
        'urls'    => array_values(array_filter(explode(',', (string) env('DIAGNOSTICS_WEBHOOK_URLS', '')))),
        'timeout' => (int) env('DIAGNOSTICS_WEBHOOK_TIMEOUT', 5),
    ],
];
```

---

## Usage

### 1. Recording Recoverable / Operational Events

```php
use AlexKassel\LaravelActionableDiagnostics\Facades\Diagnostics;

Diagnostics::recoverable(
    code: 'ERR_RATE_LIMIT_EXCEEDED',
    message: 'Upstream vendor API returned 429 Too Many Requests',
    context: ['vendor' => 'stripe', 'retry_after_seconds' => 60],
    remediationSteps: [
        'Check upstream vendor status page',
        'Verify exponential backoff settings in config/services.php',
    ],
    agentInstructions: 'Inspect rate limit headers and apply backoff multiplier.'
);
```

### 2. Actionable Exceptions

```php
use AlexKassel\LaravelActionableDiagnostics\Exceptions\BaseActionableException;
use AlexKassel\LaravelActionableDiagnostics\Enums\ErrorSeverityEnum;

class UpstreamServiceUnavailableException extends BaseActionableException
{
    protected string $diagnosticCode = 'ERR_UPSTREAM_UNAVAILABLE';
    protected ErrorSeverityEnum $severity = ErrorSeverityEnum::FATAL;
    protected array $remediationSteps = [
        'Verify network connectivity and firewall rules',
        'Check DNS resolution for api.vendor.com',
    ];
    protected string $agentInstructions = 'Review upstream connection logs and test endpoint with curl.';
}
```

```php
use AlexKassel\LaravelActionableDiagnostics\Facades\Diagnostics;

try {
    // Risky upstream call
} catch (UpstreamServiceUnavailableException $e) {
    Diagnostics::fatal($e);
}
```

---

## Testing

From the monorepo root, run the complete package verification pipeline:

```bash
composer pkg:check alex-kassel/laravel-actionable-diagnostics --json
```

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [Security Policies](https://github.com/alex-kassel/laravel-actionable-diagnostics/security/policy) on how to report vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
