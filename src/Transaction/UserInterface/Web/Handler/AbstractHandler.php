<?php

declare(strict_types=1);

namespace App\Transaction\UserInterface\Web\Handler;

use Twig\Environment;
use Slim\Psr7\Response;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Psr\Log\LoggerInterface;
use Twig\Error\RuntimeError;
use Psr\Http\Message\ResponseInterface;
use Fig\Http\Message\StatusCodeInterface;

abstract class AbstractHandler
{
    private ResponseInterface $response;

    public function __construct(
        private readonly Environment $twig,
        protected readonly LoggerInterface $logger
    ) {
        $this->response = new Response();
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function responseWithTwig(
        string $path,
        array $context = [],
        int $statusCode = StatusCodeInterface::STATUS_OK,
        string $contentType = 'text/html',
        array $headers = []
    ): ResponseInterface {
        $this->logger->debug(
            'Rendering template',
            [
                'path' => $path,
                'context' => $context,
                'statusCode' => $statusCode,
                'contentType' => $contentType,
                'headers' => $headers,
            ]
        );

        $response = $this->response->withStatus($statusCode)->withHeader('Content-Type', $contentType);

        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        $response->getBody()->write($this->twig->render($path, $context));

        return $response;
    }
}
