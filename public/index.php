<?php

// phpcs:disable

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Transaction\FinanceSystem;
use Symfony\Component\Dotenv\Dotenv;
use App\Transaction\UserInterface\Web\FinanceSystemInterface;

try {
    // Decline static file requests back to the PHP built-in webserver
    if (php_sapi_name() === 'cli-server') {
        $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)); // @phpstan-ignore-line
        if (is_string($path) && __FILE__ !== $path && is_file($path)) {
            return false;
        }
        unset($path);
    }

    $envFile = __DIR__ . '/../.env';
    if (is_file($envFile) && is_readable($envFile)) {
        $dotenv = new Dotenv();
        $dotenv->usePutenv()->load($envFile);
    }

    $settings = require __DIR__ . '/../config/settings.php';
    $dependencies = require __DIR__ . '/../config/dependencies.php';
    $middlewares = require __DIR__ . '/../config/middlewares.php';

    FinanceSystem::getInstance(
        new FinanceSystemInterface(
            $settings,
            $dependencies,
            $middlewares
        )
    )->run();
} catch (Throwable $e) {
    error_log(sprintf("WTF?!\n%s\n", $e->getMessage()));
}
