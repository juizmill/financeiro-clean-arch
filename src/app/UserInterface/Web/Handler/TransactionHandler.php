<?php

declare(strict_types=1);

namespace App\UserInterface\Web\Handler;

use Slim\App;
use Twig\Environment;
use App\Transaction\Input;
use Psr\Log\LoggerInterface;
use App\UserInterface\Web\Route;
use App\UserInterface\Web\Method;
use App\UseCase\CreateTransaction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Transaction\Store\TransactionRepositoryInterface;

class TransactionHandler extends AbstractHandler
{
    public function __construct(
        protected App $app,
        protected Environment $twig,
        protected LoggerInterface $logger,
        protected CreateTransaction $createTransaction,
        protected TransactionRepositoryInterface $transactionRepository,
    ) {
        parent::__construct($app, $twig, $logger);
    }

    #[Route(Method::GET, '/transactions', 'transactions')]
    public function index(): ResponseInterface
    {
        $transactions = $this->transactionRepository->getTransactions();

        return $this->responseWithTwig('transactions/index.twig', compact('transactions'));
    }

    #[Route(Method::GET, '/transaction/create', 'transaction.create')]
    public function create(): ResponseInterface
    {
        $this->logger->debug('Rendering page create transactions');

        return $this->responseWithTwig('transactions/create.twig');
    }

    #[Route(Method::POST, '/transaction/store', 'transaction.store')]
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->debug('Rendering page store transactions');

        $input = new Input($request->getParsedBody()); // @phpstan-ignore-line

        $transaction = $this->createTransaction->execute($input);

        return $this->redirect('/transactions');
    }

    #[Route(Method::GET, '/transaction/{id}/edit', 'transaction.edit')]
    public function edit(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->debug('Rendering page edit transactions');

        $id = (int) $request->getAttribute('id');

        $transaction = $this->transactionRepository->getById($id);

        return $this->responseWithTwig('transactions/edit.twig', compact('transaction'));
    }

    #[Route(Method::POST, '/transaction/{id}/update', 'transaction.update')]
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->debug('Rendering page update transactions');

        $id = (int) $request->getAttribute('id');

        $transaction = $this->transactionRepository->getById($id);

        if (null === $transaction) {
            return $this->redirect('/transactions');
        }

        $data = $request->getParsedBody();
        $data['id'] = $id;
        $input = new Input($data); // @phpstan-ignore-line
        $this->transactionRepository->save($input);

        return $this->redirect('/transactions');
    }

    #[Route(Method::GET, '/transaction/{id}/delete', 'transaction.destroy')]
    public function destroy(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->debug('Rendering page destroy transactions');

        $id = (int) $request->getAttribute('id');

        $transaction = $this->transactionRepository->getById($id);

        if (null === $transaction) {
            return $this->redirect('/transactions');
        }

        $this->transactionRepository->delete($id);

        return $this->redirect('/transactions');
    }
}
