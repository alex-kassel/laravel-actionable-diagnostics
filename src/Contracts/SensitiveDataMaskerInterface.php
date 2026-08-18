<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Contracts;

interface SensitiveDataMaskerInterface
{
    /**
     * Recursively mask sensitive key-value pairs in arrays.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function mask(array $data): array;
}
