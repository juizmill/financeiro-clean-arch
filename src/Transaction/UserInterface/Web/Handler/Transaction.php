<?php

declare(strict_types=1);

namespace App\Transaction\UserInterface\Web\Handler;

use Twig\Environment;
use Psr\Log\LoggerInterface;
use App\Transaction\Domain\Input;
use Psr\Http\Message\ResponseInterface;
use App\Transaction\UserInterface\Web\Route;
use Psr\Http\Message\ServerRequestInterface;
use App\Transaction\UserInterface\Web\Method;
use App\Transaction\UseCase\CreateTransaction;
use App\Transaction\Domain\Store\TransactionRepositoryInterface;

class Transaction extends AbstractHandler
{
    public function __construct(
        Environment $twig,
        LoggerInterface $logger,
        protected CreateTransaction $createTransaction,
        protected TransactionRepositoryInterface $transactionRepository
    ) {
        parent::__construct($twig, $logger);
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

        dd($transaction->getOutput());
    }
}
