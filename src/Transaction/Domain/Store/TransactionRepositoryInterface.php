<?php

declare(strict_types=1);

namespace App\Transaction\Domain\Store;

use App\Transaction\Domain\Input;
use App\Transaction\Domain\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * Retrieves a transaction by ID.
     *
     * @param int $id the transaction ID to retrieve
     *
     * @return Transaction|null the transaction if found, or null if not
     */
    public function getById(int $id): ?Transaction;

    /**
     * Returns an iterable of all transactions.
     *
     * @return iterable<Transaction>
     */
    public function getTransactions(): iterable;

    /**
     * Saves a transaction to the repository.
     *
     * @param Input $input the transaction data to save
     */
    public function save(Input $input): Transaction;
}
