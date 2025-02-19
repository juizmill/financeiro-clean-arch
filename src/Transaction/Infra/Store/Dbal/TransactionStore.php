<?php

declare(strict_types=1);

namespace App\Transaction\Infra\Store\Dbal;

use Throwable;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use App\Transaction\Domain\Input;
use DateMalformedStringException;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Transaction\Domain\Transaction;
use App\Transaction\Domain\Store\TransactionRepositoryInterface;

class TransactionStore implements TransactionRepositoryInterface
{
    public function __construct(
        protected LoggerInterface $logger,
        protected Connection $connection
    ) {
    }

    /** {@inheritdoc} */
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
        } catch (\Throwable $e) {
            $this->logger->error($e->getTraceAsString());
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getTransactions(): iterable
    {
        $transactions = [];

        try {
            $queryBuilder = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('transactions', 't');

            $this->logQuery($queryBuilder);

            $results = $queryBuilder->executeQuery()->fetchAllAssociative();

            if (count($results) > 0) {
                foreach ($results as $result) {
                    $transactions[] = self::dataTransform($result);
                }
            }

            return $transactions;
        } catch (\Throwable $e) {
            $this->logger->error($e->getTraceAsString());
        }

        return $transactions;
    }

    /**
     * {@inheritdoc}
     * @throws Throwable
     */
    public function save(Input $input): Transaction
    {
        $transaction = $input->extract();

        if ($transaction->getId() !== null) {
            return self::update($input);
        }

        return self::insert($input);
    }

    /**
     * Insert a transaction into the database.
     *
     * @param Input $input the data to insert
     *
     * @return Transaction the inserted transaction
     *
     * @throws Throwable
     */
    public function insert(Input $input): Transaction
    {
        $transaction = $input->extract();
//
//        $this->connection->beginTransaction();

        try {
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
                ->setParameter('payed', $transaction->isPayed())
                ->setParameter('type', $transaction->getType()->value)
                ->setParameter('paymentDate', $transaction->getPaymentDate())
                ->setParameter('dueDate', $transaction->getDueDate())
                ->setParameter('description', $transaction->getDescription());

            $this->logQuery($queryBuilder);

            $queryBuilder->executeQuery();

            $id = (int) $this->connection->lastInsertId();

            $transaction->setId($id);

            return $transaction;
        } catch (Throwable $e) {
            $this->logger->error($e->getTraceAsString());
//            $this->connection->rollBack();
        }

        return $transaction;
    }

    /**
     * Update a transaction on database.
     *
     * @param Input $input the input data to update
     *
     * @throws Throwable
     */
    private function update(Input $input): Transaction
    {
        $transaction = $input->extract();

        $this->connection->beginTransaction();

        try {
            $queryBuilder = $this->connection->createQueryBuilder();

            $queryBuilder
                ->update('transactions t')
                ->set('t.name', ':name')
                ->set('t.amount', ':amount')
                ->set('t.payed', ':payed')
                ->set('t.type', ':type')
                ->set('t.payment_date', ':paymentDate')
                ->set('t.due_date', ':dueDate')
                ->set('t.description', ':description')
                ->where('t.id = :id')
                ->setParameter('name', $transaction->getName())
                ->setParameter('amount', $transaction->getAmount())
                ->setParameter('payed', $transaction->isPayed())
                ->setParameter('type', $transaction->getType()->value)
                ->setParameter('paymentDate', $transaction->getPaymentDate())
                ->setParameter('dueDate', $transaction->getDueDate())
                ->setParameter('description', $transaction->getDescription())
                ->setParameter('id', $transaction->getId());

            $this->logQuery($queryBuilder);

            $queryBuilder->executeQuery();

            return $transaction;
        } catch (Throwable $e) {
            $this->logger->error($e->getTraceAsString());
            $this->connection->rollBack();
        }

        return $transaction;
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
     * @return Transaction                  the transformed Transaction object
     * @throws DateMalformedStringException
     */
    private static function dataTransform(array $result): Transaction
    {
        $result['paymentDate'] = $result['payment_date'];
        $result['dueDate'] = $result['due_date'];

        // @phpstan-ignore-next-line
        return (new Input($result))->extract();
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
}
