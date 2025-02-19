<?php

declare(strict_types=1);

return static function (): array {
    $appEnv = getenv('APP_ENV');
    $appName = getenv('APP_NAME');
    if ($appName === false) {
        $appName = 'Finance System';
    }

    if ($appEnv !== 'PRODUCTION' && $appEnv !== 'STAGE') {
        $appEnv = 'DEVELOPMENT';
    }

    $settings = [
        'di_compilation_path' => '',
        'twig_cache' => '',
        'twig_debug' => $appEnv === 'DEVELOPMENT',
        'display_error_details' => $appEnv === 'DEVELOPMENT',
        'log_errors' => true,

        'app' => [
            'name' => $appName,
            'env' => $appEnv,
            'data' => getenv('APP_DATA'),
        ],

        'logger' => [
            'name' => $appName,
            'path' => '../var/log/app/app.log',
            'level' => $appEnv === 'PRODUCTION' ? 400 : 100,
        ],

        'database' => [
            'db' => [
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'dbname' => getenv('DB_DATABASE'),
                'user' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'driver' => 'pdo_pgsql', // 'pdo_mysql', 'pdo_sqlite'
            ],
        ],
    ];

    if ($appEnv !== 'PRODUCTION') {
        $settings['di_compilation_path'] = '';
        $settings['twig_cache'] = '';
        $settings['display_error_details'] = true;
        $settings['logger']['level'] = 100;
    }

    return $settings;
};
