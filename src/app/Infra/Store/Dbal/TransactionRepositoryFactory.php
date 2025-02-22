<?php

declare(strict_types=1);

namespace App\Infra\Store\Dbal;

use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use App\Transaction\Store\AbstractRepositoryFactory;
use App\Transaction\Store\TransactionRepositoryInterface;

class TransactionRepositoryFactory extends AbstractRepositoryFactory
{
    public function __construct(protected LoggerInterface $logger, protected Connection $connection)
    {
        parent::__construct($this->logger);
    }

    public function transactionRepository(): TransactionRepositoryInterface
    {
        return new TransactionStore($this->logger, $this->connection);
    }
}
