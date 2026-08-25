<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Services;

use AlexKassel\LaravelActionableDiagnostics\Contracts\SensitiveDataMaskerInterface;

class SensitiveDataMasker implements SensitiveDataMaskerInterface
{
    /** @var array<int, string> */
    protected array $sensitiveKeys;

    protected string $redactionText;

    /**
     * @param  array<int, string>  $sensitiveKeys
     */
    public function __construct(array $sensitiveKeys = [], string $redactionText = '***REDACTED***')
    {
        $this->sensitiveKeys = ! empty($sensitiveKeys) ? array_values($sensitiveKeys) : [
            'password', 'pass', 'secret', 'bearer', 'token',
            'api_key', 'authorization', 'credit_card', 'ssn',
            'private_key', 'cookie', 'db_password',
        ];
        $this->redactionText = $redactionText;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function mask(array $data): array
    {
        return $this->maskRecursive($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function maskRecursive(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $keyString = (string) $key;

            if ($this->isSensitiveKey($keyString)) {
                $result[$keyString] = $this->redactionText;
            } elseif (is_array($value)) {
                /** @var array<string, mixed> $value */
                $result[$keyString] = $this->maskRecursive($value);
            } else {
                $result[$keyString] = $value;
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
