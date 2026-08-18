<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Services;

use AlexKassel\LaravelActionableDiagnostics\Contracts\SensitiveDataMaskerInterface;

class SensitiveDataMasker implements SensitiveDataMaskerInterface
{
    /** @var array<int, string> */
    protected array $sensitiveKeys;
    protected string $redactionText;

    public function __construct(array $sensitiveKeys = [], string $redactionText = '***REDACTED***')
    {
        $this->sensitiveKeys = ! empty($sensitiveKeys) ? $sensitiveKeys : [
            'password', 'pass', 'secret', 'bearer', 'token',
            'api_key', 'authorization', 'credit_card', 'ssn',
            'private_key', 'cookie', 'db_password',
        ];
        $this->redactionText = $redactionText;
    }

    public function mask(array $data): array
    {
        return $this->maskRecursive($data);
    }

    private function maskRecursive(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $keyString = (string) $key;

            if ($this->isSensitiveKey($keyString)) {
                $result[$key] = $this->redactionText;
            } elseif (is_array($value)) {
                $result[$key] = $this->maskRecursive($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function isSensitiveKey(string $key): bool
    {
        $lowercaseKey = strtolower($key);

        foreach ($this->sensitiveKeys as $sensitive) {
            if (str_contains($lowercaseKey, strtolower($sensitive))) {
                return true;
            }
        }

        return false;
    }
}
