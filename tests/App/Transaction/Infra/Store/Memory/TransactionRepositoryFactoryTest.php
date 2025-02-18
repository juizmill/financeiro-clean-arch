<?php

namespace App\Transaction\Infra\Store\Memory;

use Psr\Log\LoggerInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use App\Transaction\Domain\Store\AbstractRepositoryFactory;
use App\Transaction\Domain\Store\TransactionRepositoryInterface;

class TransactionRepositoryFactoryTest extends TestCase
{
    protected LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    #[Test]
    #[TestDox('Should create transaction repository')]
    public function shouldCreateTransactionRepository(): void
    {
        $factory = new TransactionRepositoryFactory($this->logger);
        $repository = $factory->transactionRepository();

        Assert::assertInstanceOf(AbstractRepositoryFactory::class, $factory);
        Assert::assertInstanceOf(TransactionRepositoryInterface::class, $repository);
    }
}
