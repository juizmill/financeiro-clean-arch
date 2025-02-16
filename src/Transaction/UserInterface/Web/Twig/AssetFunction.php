<?php

declare(strict_types=1);

namespace App\Transaction\UserInterface\Web\Twig;

use InvalidArgumentException;

readonly class AssetFunction
{
    protected const string PUBLIC_PATH = 'public';

    public function publicPath(string $path): ?string
    {
        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }

        $parsedPath = sprintf('%s/%s/%s', realpath('.'), self::PUBLIC_PATH, $path);

        if (! file_exists($parsedPath)) {
            $parsedPath = sprintf('../%s/%s', self::PUBLIC_PATH, $path);

            if (! file_exists($parsedPath)) {
                throw new InvalidArgumentException("File with path $path not exists.");
            }
        }

        $httpHost = sprintf(
            '%s://%s',
            isset($_SERVER['HTTPS']) ? 'https' : 'http',
            $_SERVER['HTTP_HOST'] ?? '' // @phpstan-ignore-line
        );

        if (filter_var($httpHost, FILTER_VALIDATE_URL) === false) {
            $httpHost = getenv('FRONT_URL');
        }

        return "$httpHost/$path";
    }
}
