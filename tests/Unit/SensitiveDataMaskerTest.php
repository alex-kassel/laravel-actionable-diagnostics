<?php

declare(strict_types=1);

namespace AlexKassel\LaravelActionableDiagnostics\Tests\Unit;

use AlexKassel\LaravelActionableDiagnostics\Services\SensitiveDataMasker;
use PHPUnit\Framework\TestCase;

class SensitiveDataMaskerTest extends TestCase
{
    public function test_it_masks_sensitive_keys_recursively(): void
    {
        $masker = new SensitiveDataMasker;

        $input = [
            'username' => 'alex',
            'password' => 'secret123',
            'nested' => [
                'bearer_token' => 'xyz-token-value',
                'public_data' => 42,
            ],
        ];

        $output = $masker->mask($input);

        $this->assertSame('alex', $output['username']);
        $this->assertSame('***REDACTED***', $output['password']);
        $this->assertIsArray($output['nested']);
        $this->assertSame('***REDACTED***', $output['nested']['bearer_token']);
        $this->assertSame(42, $output['nested']['public_data']);
    }
}
