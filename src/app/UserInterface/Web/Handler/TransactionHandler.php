<?php

declare(strict_types=1);

namespace App\UserInterface\Web\Handler;

use Slim\App;
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
        protected LoggerInterface $logger,
        protected CreateTransaction $createTransaction,
        protected TransactionRepositoryInterface $transactionRepository,
    ) {
        parent::__construct($app, $logger);
    }

    #[Route(Method::GET, '/transactions', 'transactions')]
    public function index(): ResponseInterface
    {
        $transactions = $this->transactionRepository->getTransactions();

        return $this->json($transactions);
    }

    #[Route(Method::GET, '/transaction/{id}', 'transaction.get')]
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id'); // @phpstan-ignore-line

        $transaction = $this->transactionRepository->getById($id);

        if (null === $transaction) {
            return $this->json([
                'message' => 'Transaction not found',
            ])->withStatus(404);
        }

        return $this->json($transaction->toArray());
    }

    #[Route(Method::POST, '/transaction/store', 'transaction.store')]
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $input = new Input($request->getParsedBody()); // @phpstan-ignore-line

        $transaction = $this->createTransaction->execute($input)->getOutput();
        if (null === $transaction) {
            return $this->json('Transaction not created')->withStatus(400);
        }

        return $this->json($transaction->toArray())->withStatus(201);
    }

    #[Route(Method::PUT, '/transaction/{id}/update', 'transaction.update')]
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id'); // @phpstan-ignore-line

        $transaction = $this->transactionRepository->getById($id);

        if (null === $transaction) {
            return $this->json([
                'message' => 'Transaction not found',
            ])->withStatus(404);
        }

        $data = $request->getParsedBody();
        $data['id'] = $id; // @phpstan-ignore-line
        $input = new Input($data); // @phpstan-ignore-line
        $transaction = $this->transactionRepository->save($input);

        return $this->json($transaction)->withStatus(201);
    }

    #[Route(Method::GET, '/transaction/{id}/delete', 'transaction.destroy')]
    public function destroy(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id'); // @phpstan-ignore-line

        $transaction = $this->transactionRepository->getById($id);

        if (null === $transaction) {
            return $this->json([
                'message' => 'Transaction not found',
            ])->withStatus(404);
        }

        $this->transactionRepository->delete($id);

        return $this->json()->withStatus(204);
    }
}
