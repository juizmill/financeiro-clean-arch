<?php

declare(strict_types=1);

namespace App\Transaction\Domain;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

class InputTest extends TestCase
{
    /**
     * @return array{
     *     name: string,
     *     amount: float|int|string,
     *     payed: bool|int,
     *     type: string,
     *     paymentDate: ?string,
     *     dueDate: ?string,
     *     description: ?string
     * }
     */
    protected static function data(): array
    {
        return [
            'name' => 'name',
            'amount' => 100.0,
            'payed' => true,
            'type' => 'income',
            'paymentDate' => '2022-01-01',
            'dueDate' => '2022-01-01',
            'description' => 'description',
        ];
    }

    #[Test]
    #[TestDox('Should extract input data')]
    public function shouldExtractInputData(): void
    {
        $data = self::data();
        $input = new Input($data); // @phpstan-ignore-line
        $transaction = $input->extract();

        Assert::assertInstanceOf(Transaction::class, $transaction);
        Assert::assertSame('name', $transaction->getName());
    }

    #[Test]
    #[TestDox('Should extract input data with id')]
    public function shouldExtractInputDataWithId(): void
    {
        $data = self::data();
        $data['id'] = 1;
        $input = new Input($data);
        $transaction = $input->extract();

        Assert::assertInstanceOf(Transaction::class, $transaction);
        Assert::assertSame(1, $transaction->getId());
    }
}
