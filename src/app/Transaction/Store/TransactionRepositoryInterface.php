<?php

declare(strict_types=1);

namespace App\Transaction\Store;

use App\Transaction\Input;
use App\Transaction\Transaction;

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
     * @return array<int, Transaction>|Transaction[] the transactions
     */
    public function getTransactions(): iterable;

    /**
     * Saves a transaction to the repository.
     *
     * @param  Input                     $input the transaction data to save
     * @return array<string, mixed>|null the saved transactions
     */
    public function save(Input $input): ?iterable;

    /**
     * Deletes a transaction from the repository.
     *
     * @param int $id the transaction ID to delete
     */
    public function delete(int $id): void;
}
