<?php

declare(strict_types=1);

namespace App\Transaction;

use LogicException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
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
            name: 'name',
            amount: 100.0,
            payed: true,
            typeEnum: TypeEnum::INCOME,
            paymentDate: $paymentDate,
            dueDate: $dueDate,
            description: 'description'
        );

        $transaction->setId(1);

        Assert::assertSame(1, $transaction->getId());
        Assert::assertSame('name', $transaction->getName());
        Assert::assertSame(100.0, $transaction->getAmount());
        Assert::assertSame(true, $transaction->isPayed());
        Assert::assertSame(TypeEnum::INCOME, $transaction->getType());
        Assert::assertSame($paymentDate, $transaction->getPaymentDate());
        Assert::assertSame($dueDate, $transaction->getDueDate());
        Assert::assertSame('description', $transaction->getDescription());
    }

    #[Test]
    #[TestDox('Should exception amount on negative value')]
    public function testShouldExceptionAmount()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        new Transaction(
            name: 'name',
            amount: -1,
            payed: true,
            typeEnum: TypeEnum::INCOME,
            paymentDate: new \DateTimeImmutable(),
            dueDate: new \DateTimeImmutable(),
            description: 'description'
        );
    }

    #[Test]
    #[TestDox('Should exception id must be greater than 0')]
    public function shouldExceptionId()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Id must be greater than 0');

        $transaction = new Transaction(
            name: 'name',
            amount: 11,
            payed: true,
            typeEnum: TypeEnum::INCOME,
            paymentDate: new \DateTimeImmutable(),
            dueDate: new \DateTimeImmutable(),
            description: 'description'
        );

        $transaction->setId(0);
    }
}
