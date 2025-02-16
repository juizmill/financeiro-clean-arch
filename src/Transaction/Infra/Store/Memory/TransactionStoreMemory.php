<?php

declare(strict_types=1);

namespace App\Transaction\Infra\Store\Memory;

use App\Transaction\Domain\Input;
use App\Transaction\Domain\Transaction;
use App\Transaction\Domain\Store\TransactionRepositoryInterface;

class TransactionStoreMemory implements TransactionRepositoryInterface
{
    /** @var array<int, Transaction> */
    private array $transactions = [];

    public function getById(int $id): ?Transaction
    {
        return $this->transactions[$id] ?? null;
    }

    public function getTransactions(): iterable
    {
        return $this->transactions;
    }

    public function save(Input $input): Transaction
    {
        $transaction = $input->extract();

        if (isset($this->transactions[$transaction->getId()])) {
            $this->transactions[(int) $transaction->getId()] = $transaction;

            return $transaction;
        }

        $id = count($this->transactions) <= 0 ? 1 : max(array_keys($this->transactions)) + 1;
        $transaction->setId($id);

        $this->transactions[$id] = $transaction;

        return $transaction;
    }
}
