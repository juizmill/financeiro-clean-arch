<?php

declare(strict_types=1);

namespace App\Infra\Store\Memory;

use App\Transaction\Input;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use App\Transaction\Transaction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use App\Transaction\Store\TransactionRepositoryInterface;

class ProductStoreMemoryTest extends TestCase
{
    // @phpstan-ignore-next-line
    protected function input(array $data = []): Input
    {
        $data = array_merge([
            'name' => 'name',
            'amount' => 100.0,
            'payed' => true,
            'type' => 'income',
            'paymentDate' => '2022-01-01',
            'dueDate' => '2022-01-01',
            'description' => 'description',
        ], $data);

        return new Input($data); // @phpstan-ignore-line
    }

    #[Test]
    #[TestDox('Should implement TransactionRepositoryInterface')]
    public function shouldImplementTransactionRepository(): void
    {
        $productStoreMemory = new TransactionStoreMemory();

        Assert::assertInstanceOf(TransactionRepositoryInterface::class, $productStoreMemory);
    }

    #[Test]
    #[TestDox('Should get by id transaction')]
    public function shouldGetTransactionById(): void
    {
        $productStoreMemory = new TransactionStoreMemory();

        $transaction = $productStoreMemory->save($this->input());
        $result = $productStoreMemory->getById($transaction->getId());

        Assert::assertInstanceOf(Transaction::class, $result);
    }

    #[Test]
    #[TestDox('Should save a transaction')]
    public function shouldSaveTransaction(): void
    {
        $productStoreMemory = new TransactionStoreMemory();

        $transaction = $productStoreMemory->save($this->input());

        Assert::assertInstanceOf(Transaction::class, $transaction);
        Assert::assertEquals(1, $transaction->getId());
    }

    #[Test]
    #[TestDox('Should save a transaction update')]
    public function shouldSaveTransactionUpdate(): void
    {
        $productStoreMemory = new TransactionStoreMemory();
        $productStoreMemory->save($this->input());

        $transaction = $productStoreMemory->save($this->input([
            'id' => 1,
            'name' => 'new name',
        ]));

        Assert::assertInstanceOf(Transaction::class, $transaction);
        Assert::assertEquals(1, $transaction->getId());
        Assert::assertEquals('new name', $transaction->getName());
    }

    #[Test]
    #[TestDox('Should get all transactions')]
    public function shouldGetAllTransactions(): void
    {
        $productStoreMemory = new TransactionStoreMemory();

        Assert::assertCount(0, $productStoreMemory->getTransactions());

        $productStoreMemory->save($this->input());

        Assert::assertCount(1, $productStoreMemory->getTransactions());
    }
}
