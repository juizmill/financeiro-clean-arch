<?php

declare(strict_types=1);

namespace App\Transaction;

class TransactionDecorator extends Transaction
{
    private ?string $createdAt = null;

    private ?string $updatedAt = null;

    public function __construct(
        protected readonly Transaction $transaction
    ) {
        parent::__construct(
            $this->transaction->getName(),
            $this->transaction->getAmount(),
            $this->transaction->isPayed(),
            $this->transaction->getType(),
            $this->transaction->getPaymentDate(),
            $this->transaction->getDueDate(),
            $this->transaction->getDescription()
        );
    }

    public function setCreatedAt(?string $createdAt = null): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(?string $updatedAt = null): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        $data['createdAt'] = $this->createdAt;
        $data['updatedAt'] = $this->updatedAt;

        return $data;
    }
}
