<?php

declare(strict_types=1);

namespace App\Transaction;

use Psr\Log\LoggerInterface;
use App\Transaction\Store\AbstractRepositoryFactory;

abstract class UseCase
{
    protected ?Transaction $output = null;

    public function __construct(
        protected LoggerInterface $logger,
        protected AbstractRepositoryFactory $repositoryFactory
    ) {
    }

    abstract public function execute(?Input $input = null): UseCase;

    public function getOutput(): ?Transaction
    {
        return $this->output;
    }
}
