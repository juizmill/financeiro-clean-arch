<?php

declare(strict_types=1);

use Slim\App;

return static function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();

    $errorMiddleware = $app->addErrorMiddleware(
        true,
        true,
        true,
    );

    $app->addMiddleware($errorMiddleware);
};
