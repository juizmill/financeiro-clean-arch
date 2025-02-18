<?php

declare(strict_types=1);

namespace App\Transaction\Infra\Store\Memory;

use App\Transaction\Domain\Store\AbstractRepositoryFactory;
use App\Transaction\Domain\Store\TransactionRepositoryInterface;

class TransactionRepositoryFactory extends AbstractRepositoryFactory
{
    public function transactionRepository(): TransactionRepositoryInterface
    {
        return new TransactionStoreMemory();
    }
}
