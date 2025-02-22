<?php

declare(strict_types=1);

namespace App\Infra\Store\Memory;

use App\Transaction\Store\AbstractRepositoryFactory;
use App\Transaction\Store\TransactionRepositoryInterface;

class TransactionRepositoryFactory extends AbstractRepositoryFactory
{
    public function transactionRepository(): TransactionRepositoryInterface
    {
        return new TransactionStoreMemory();
    }
}
