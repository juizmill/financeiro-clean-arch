<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Transaction\Input;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use App\Transaction\Transaction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use App\Transaction\Store\AbstractRepositoryFactory;
use App\Transaction\Store\TransactionRepositoryInterface;

class CreateTransactionTest extends TestCase
{
    protected LoggerInterface|MockObject $logger;

    protected AbstractRepositoryFactory|MockObject $repositoryFactory;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->repositoryFactory = $this->getMockBuilder(AbstractRepositoryFactory::class)
            ->setConstructorArgs([$this->logger])
            ->getMock();
    }

    protected static function input(): Input
    {
        // @phpstan-ignore-next-line
        return new Input([
            'name' => 'name',
            'amount' => 100.0,
            'payed' => true,
            'type' => 'income',
            'paymentDate' => '2022-01-01',
            'dueDate' => '2022-01-01',
            'description' => 'description',
        ]);
    }

    #[Test]
    #[TestDox('Should execute use case')]
    public function shouldExecute()
    {
        $repository = $this->getMockBuilder(TransactionRepositoryInterface::class)->getMock();
        $repository->expects($this->once())->method('save');

        $this->repositoryFactory->expects($this->once())->method('transactionRepository')
            ->willReturn($repository);

        $useCase = new CreateTransaction($this->logger, $this->repositoryFactory);
        $result = $useCase->execute(self::input());

        Assert::assertInstanceOf(CreateTransaction::class, $result);
        Assert::assertInstanceOf(Transaction::class, $result->getOutput());
    }

    #[Test]
    #[TestDox('Should throw exception input is required')]
    public function shouldThrowExceptionInputIsRequired()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input is required');

        $useCase = new CreateTransaction($this->logger, $this->repositoryFactory);
        $useCase->execute();
    }
}
