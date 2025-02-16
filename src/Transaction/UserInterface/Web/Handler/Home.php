<?php

declare(strict_types=1);

namespace App\Transaction\UserInterface\Web\Handler;

use Psr\Http\Message\ResponseInterface;
use App\Transaction\UserInterface\Web\Route;
use App\Transaction\UserInterface\Web\Method;

class Home extends AbstractHandler
{
    #[Route(Method::GET, '/', 'home')]
    public function index(): ResponseInterface
    {
        $this->logger->debug('Rendering home page');

        return $this->responseWithTwig('home/index.twig');
    }
}
