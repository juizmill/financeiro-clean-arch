<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Transaction\Input;
use App\Transaction\UseCase;
use InvalidArgumentException;

class CreateTransaction extends UseCase
{
    public function execute(?Input $input = null): UseCase
    {
        if (! $input instanceof Input) {
            throw new InvalidArgumentException('Input is required');
        }

        $this->logger->debug('Creating transaction', ['input' => $input]);

        $repository = $this->repositoryFactory->transactionRepository();
        $this->output = $repository->save($input);

        return $this;
    }
}
