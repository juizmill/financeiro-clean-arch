<?php

declare(strict_types=1);

namespace App\Transaction;

use LogicException;
use DateTimeInterface;

class Transaction
{
    protected ?int $id = null;

    public function __construct(
        private readonly string $name,
        private readonly float $amount,
        private readonly bool $payed,
        private readonly TypeEnum $typeEnum,
        private readonly ?DateTimeInterface $paymentDate = null,
        private readonly ?DateTimeInterface $dueDate = null,
        private readonly ?string $description = null,
    ) {
        if ($this->amount <= 0) {
            throw new LogicException('Amount must be greater than 0');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        if ($id <= 0) {
            throw new LogicException('Id must be greater than 0');
        }

        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function isPayed(): bool
    {
        return $this->payed;
    }

    public function getType(): TypeEnum
    {
        return $this->typeEnum;
    }

    public function getPaymentDate(): ?DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function getDueDate(): ?DateTimeInterface
    {
        return $this->dueDate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Converts the transaction object to an associative array.
     *
     * This method is useful when you need to convert a Transaction object to an array, which can be
     * easily serialized, stored in a database, or sent over the wire.
     *
     * @return array<string, mixed> an associative array containing the transaction data
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'payed' => $this->payed,
            'type' => $this->typeEnum->value,
            'paymentDate' => $this->paymentDate,
            'dueDate' => $this->dueDate,
            'description' => $this->description,
        ];
    }
}
