<?php

declare(strict_types=1);

namespace App\UserInterface\Web\Handler;

use Slim\App;
use Slim\Psr7\Response;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractHandler
{
    protected ResponseInterface $response;

    /**
     * Initializes the handler with the Slim app and logger.
     *
     * @param App<ContainerInterface> $app    the Slim app
     * @param LoggerInterface         $logger the logger
     */
    public function __construct(
        protected App $app,
        protected LoggerInterface $logger
    ) {
        $this->response = (new Response())->withHeader('Content-Type', 'application/json');
    }

    /**
     * Creates a JSON response with the given content.
     *
     * @param array<int|string, mixed>|string|null $content the content to encode as JSON
     *
     * @return ResponseInterface the response with the JSON content
     */
    protected function json(array|string|null $content): ResponseInterface
    {
        $response = $this->response;

        $content = match (true) {
            is_array($content) => json_encode($content),
            is_string($content) => $content,
            default => '',
        };

        $response->getBody()->write((string) $content);

        return $response;
    }
}
