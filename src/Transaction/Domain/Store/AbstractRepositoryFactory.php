<?php

declare(strict_types=1);

namespace App\Transaction\Domain\Store;

use Psr\Log\LoggerInterface;

abstract class AbstractRepositoryFactory
{
    public function __construct(protected LoggerInterface $logger)
    {
        $this->logger->debug('Creating ' . static::class);
    }

    abstract public function transactionRepository(): TransactionRepositoryInterface;
}
