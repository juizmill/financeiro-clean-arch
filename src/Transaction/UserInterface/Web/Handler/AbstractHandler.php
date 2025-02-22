<?php

declare(strict_types=1);

namespace App\Transaction\UserInterface\Web\Handler;

use Slim\App;
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
    protected ResponseInterface $response;

    public function __construct(
        protected App $app,
        protected Environment $twig,
        protected LoggerInterface $logger
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

    protected function redirect(string $path): ResponseInterface
    {
        return $this->response->withStatus(StatusCodeInterface::STATUS_FOUND)
            ->withHeader('Location', $path);
    }
}
