<?php

declare(strict_types=1);

namespace Tests\Transaction\Domain;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use App\Transaction\Domain\TypeEnum;
use PHPUnit\Framework\Attributes\Test;
use App\Transaction\Domain\Transaction;
use PHPUnit\Framework\Attributes\TestDox;

class TransactionTest extends TestCase
{
    #[Test]
    #[TestDox('Should create a transaction')]
    public function testCreateTransaction(): void
    {
        $dueDate = new \DateTimeImmutable();
        $paymentDate = new \DateTimeImmutable();

        $transaction = new Transaction(
            id: 1,
            name: 'name',
            amount:100.0,
            payed: true,
            typeEnum: TypeEnum::INCOME,
            paymentDate: $paymentDate,
            dueDate: $dueDate,
            description: 'description'
        );

        Assert::assertSame(1, $transaction->getId());
        Assert::assertSame('name', $transaction->getName());
        Assert::assertSame(100.0, $transaction->getAmount());
        Assert::assertSame(true, $transaction->isPayed());
        Assert::assertSame(TypeEnum::INCOME, $transaction->getType());
        Assert::assertSame($paymentDate, $transaction->getPaymentDate());
        Assert::assertSame($dueDate, $transaction->getDueDate());
        Assert::assertSame('description', $transaction->getDescription());
    }
}
