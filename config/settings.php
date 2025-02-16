<?php

declare(strict_types=1);

return static function (): array {
    $appEnv = getenv('APP_ENV');

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
            'name' => 'Sistema Financeiro',
            'env' => $appEnv,
            'data' => getenv('APP_DATA'),
        ],

        'logger' => [
            'name' => 'Finance System',
            'path' => '../var/log/app/app.log',
            'level' => $appEnv === 'PRODUCTION' ? 400 : 100,
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
