<?php

declare(strict_types=1);

namespace App\UserInterface\Web\Handler;

use App\UserInterface\Web\Route;
use App\UserInterface\Web\Method;
use Psr\Http\Message\ResponseInterface;

class Home extends AbstractHandler
{
    #[Route(Method::GET, '/', 'home')]
    public function index(): ResponseInterface
    {
        $this->logger->debug('Rendering home page');

        return $this->responseWithTwig('home/index.twig');
    }
}
