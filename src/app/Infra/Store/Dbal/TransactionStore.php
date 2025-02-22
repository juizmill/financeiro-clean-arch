<?php

declare(strict_types=1);

namespace App\Infra\Store\Dbal;

use Throwable;
use App\Transaction\Input;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use App\Transaction\Transaction;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Transaction\TransactionDecorator;
use App\Transaction\Store\TransactionRepositoryInterface;

class TransactionStore implements TransactionRepositoryInterface
{
    public function __construct(
        protected LoggerInterface $logger,
        protected Connection $connection,
    ) {
    }

    public function getById(int $id): ?Transaction
    {
        try {
            $queryBuilder = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('transactions', 't')
                ->where('t.id = :id')
                ->setParameter('id', $id);

            $this->logQuery($queryBuilder);

            $result = $queryBuilder->executeQuery()->fetchAssociative();

            if (! is_array($result) || count($result) === 0) {
                return null;
            }

            return self::dataTransform($result);
        } catch (Throwable $e) {
            $this->logger->error("{$e->getMessage()}\n{$e->getTraceAsString()}");
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactions(): iterable
    {
        try {
            $queryBuilder = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('transactions', 't')
                ->orderBy('t.id', 'ASC');

            $this->logQuery($queryBuilder);

            $transactions = $queryBuilder->executeQuery()->fetchAllAssociative();

            if (count($transactions) <= 0) {
                return [];
            }

            foreach ($transactions as $key => $transaction) {
                $transactions[$key] = self::dataTransform($transaction)->toArray();
            }

            return $transactions;
        } catch (Throwable $e) {
            $this->logger->error("{$e->getMessage()}\n{$e->getTraceAsString()}");

            return [];
        }
    }

    /**
     * {@inheritdoc}
     * @throws Throwable
     */
    public function save(Input $input): ?iterable
    {
        $transaction = $input->extract();

        if ($transaction->getId() !== null) {
            return self::update($input)?->toArray();
        }

        return self::insert($input)?->toArray();
    }

    /**
     * Insert a transaction into the database.
     *
     * @param Input $input the data to insert
     *
     * @return Transaction|null the inserted transaction
     *
     * @throws Throwable
     */
    public function insert(Input $input): ?Transaction
    {
        try {
            $transaction = $input->extract();
            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->insert('transactions')
                ->values([
                    'name' => ':name',
                    'amount' => ':amount',
                    'payed' => ':payed',
                    'type' => ':type',
                    'payment_date' => ':paymentDate',
                    'due_date' => ':dueDate',
                    'description' => ':description',
                ])
                ->setParameter('name', $transaction->getName())
                ->setParameter('amount', $transaction->getAmount())
                ->setParameter('payed', $transaction->isPayed() ? 1 : 0)
                ->setParameter('type', $transaction->getType()->value)
                ->setParameter('paymentDate', $transaction->getPaymentDate())
                ->setParameter('dueDate', $transaction->getDueDate())
                ->setParameter('description', $transaction->getDescription());

            $this->logQuery($queryBuilder);

            $queryBuilder->executeQuery();

            $id = (int) $this->connection->lastInsertId();

            return $this->getById($id);
        } catch (Throwable $e) {
            $this->logger->error("{$e->getMessage()}\n{$e->getTraceAsString()}");

            return null;
        }
    }

    /**
     * Update a transaction on database.
     *
     * @param Input $input the input data to update
     *
     * @throws Throwable
     */
    private function update(Input $input): ?Transaction
    {
        try {
            $transaction = $input->extract();
            $queryBuilder = $this->connection->createQueryBuilder();

            $queryBuilder
                ->update('transactions')
                ->set('name', ':name')
                ->set('amount', ':amount')
                ->set('payed', ':payed')
                ->set('type', ':type')
                ->set('payment_date', ':paymentDate')
                ->set('due_date', ':dueDate')
                ->set('description', ':description')
                ->set('updated_at', 'CURRENT_TIMESTAMP')
                ->where('id = :id')
                ->setParameter('name', $transaction->getName())
                ->setParameter('amount', $transaction->getAmount())
                ->setParameter('payed', $transaction->isPayed() ? 1 : 0)
                ->setParameter('type', $transaction->getType()->value)
                ->setParameter('paymentDate', $transaction->getPaymentDate())
                ->setParameter('dueDate', $transaction->getDueDate())
                ->setParameter('description', $transaction->getDescription())
                ->setParameter('id', $transaction->getId());

            $this->logQuery($queryBuilder);

            $queryBuilder->executeQuery();

            $id = (int) $transaction->getId();

            return $this->getById($id);
        } catch (Throwable $e) {
            $this->logger->error("{$e->getMessage()}\n{$e->getTraceAsString()}");

            return null;
        }
    }

    /**
     * Transforms a DB result row into a Transaction object.
     *
     * The column names from the database are not the same as the property names in the Transaction
     * class, so we need to transform them. This function does that and returns a new Transaction
     * object with the correct property names and values.
     *
     * @param array<string, mixed> $result a DB result row
     *
     * @return Transaction                   the transformed Transaction object
     * @throws \DateMalformedStringException
     */
    private static function dataTransform(array $result): Transaction
    {
        $result['paymentDate'] = $result['payment_date'];
        $result['dueDate'] = $result['due_date'];

        $createdAt = $result['created_at'] ?? null;
        $updatedAt = $result['updated_at'] ?? null;

        // @phpstan-ignore-next-line
        $transaction = (new Input($result))->extract();

        return (new TransactionDecorator($transaction))
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt);
    }

    /**
     * Logs a query with all the parameters.
     *
     * @param QueryBuilder $queryBuilder the query to log
     */
    private function logQuery(QueryBuilder $queryBuilder): void
    {
        $data = json_encode([
            'query' => $queryBuilder->getSQL(),
            'params' => $queryBuilder->getParameters(),
        ]);

        $this->logger->info((string) $data);
    }

    public function delete(int $id): void
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->delete('transactions')
            ->where('id = :id')
            ->setParameter('id', $id);

        $this->logQuery($queryBuilder);

        $queryBuilder->executeQuery();
    }
}
