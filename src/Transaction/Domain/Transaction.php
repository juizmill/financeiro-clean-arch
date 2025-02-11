<?php

declare(strict_types=1);

namespace App\Transaction\Domain;

use DateTimeInterface;

class Transaction
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly float $amount,
        private readonly bool $payed,
        private readonly TypeEnum $typeEnum,
        private readonly ?DateTimeInterface $paymentDate = null,
        private readonly ?DateTimeInterface $dueDate = null,
        private readonly ?string $description = null,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
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
}
