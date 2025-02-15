<?php

declare(strict_types=1);

namespace App\Transaction\Domain\Store;

use Psr\Log\LoggerInterface;

abstract readonly class AbstractRepositoryFactory
{
    public function __construct(protected LoggerInterface $logger)
    {
        $this->logger->debug('Creating ' . static::class);
    }

    abstract public function createTransactionRepository(): TransactionRepositoryInterface;
}
