<?php

declare(strict_types=1);

namespace App\Transaction;

use DateTime;
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
        $payed = false;
        if (isset($this->data['payed'])) {
            $payed = (bool) $this->data['payed'];
        }

        $paymentDate = null;
        if (isset($this->data['paymentDate']) && $this->validateDate($this->data['paymentDate'])) {
            $paymentDate = new DateTimeImmutable($this->data['paymentDate']);
        }

        $dueDate = null;
        if (isset($this->data['dueDate']) && $this->validateDate($this->data['dueDate'])) {
            $dueDate = new DateTimeImmutable($this->data['dueDate']);
        }

        $transaction = new Transaction(
            name: $this->data['name'],
            amount: (float) $this->data['amount'],
            payed: $payed,
            typeEnum: TypeEnum::from($this->data['type']),
            paymentDate: $paymentDate,
            dueDate: $dueDate,
            description: $this->data['description'] ?? null
        );

        if (isset($this->data['id'])) {
            $id = (int) $this->data['id'];
            $transaction->setId($id);
        }

        return $transaction;
    }

    private function validateDate(string $date): bool
    {
        $isValid = DateTime::createFromFormat('Y-m-d', $date) !== false;
        if (! $isValid) {
            trigger_error('Invalid date format', E_USER_WARNING);
        }

        return $isValid;
    }
}
