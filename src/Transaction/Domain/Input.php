<?php

declare(strict_types=1);

namespace App\Transaction\Domain;

use DateTimeImmutable;
use DateMalformedStringException;

class Input
{
    /**
     * @param array{
     *     id: int|string|null,
     *     name: string,
     *     amount: float|int|string,
     *     payed: bool|int,
     *     type: string,
     *     paymentDate: ?string,
     *     dueDate: ?string,
     *     description: ?string
     * } $data
     */
    public function __construct(protected array $data)
    {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function extract(): Transaction
    {
        $transaction = new Transaction(
            name: $this->data['name'],
            amount: (float) $this->data['amount'],
            payed: (bool) $this->data['payed'],
            typeEnum: TypeEnum::from($this->data['type']),
            paymentDate: new DateTimeImmutable((string) $this->data['paymentDate']),
            dueDate: new DateTimeImmutable((string) $this->data['dueDate']),
            description: $this->data['description'] ?? null
        );

        if (isset($this->data['id'])) {
            $id = (int) $this->data['id'];
            $transaction->setId($id);
        }

        return $transaction;
    }
}
