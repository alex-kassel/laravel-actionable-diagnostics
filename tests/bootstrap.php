<?php

declare(strict_types=1);
use Composer\Autoload\ClassLoader;

$candidates = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../../../vendor/autoload.php',
];

$autoloader = null;
foreach ($candidates as $candidate) {
    if (file_exists($candidate)) {
        $autoloader = require $candidate;
        break;
    }
}

if ($autoloader === null || ! $autoloader instanceof ClassLoader) {
    throw new RuntimeException('Composer autoloader not found.');
}

$autoloader->addPsr4('AlexKassel\\LaravelActionableDiagnostics\\', __DIR__.'/../src');
$autoloader->addPsr4('AlexKassel\\LaravelActionableDiagnostics\\Tests\\', __DIR__);
