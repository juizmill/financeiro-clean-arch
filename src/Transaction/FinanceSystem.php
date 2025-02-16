<?php

declare(strict_types=1);

namespace App\Transaction;

use RuntimeException;
use App\Transaction\Domain\UserInterface;

class FinanceSystem
{
    private static ?FinanceSystem $instance = null;

    private static ?UserInterface $currentUserInterface = null;

    private ?UserInterface $userInterface = null;

    private function __construct(?UserInterface $userInterface)
    {
        if (self::$currentUserInterface === null && $userInterface === null) {
            throw new RuntimeException('No UserInterface implementation provided.');
        }

        if (self::$currentUserInterface === null) {
            self::$currentUserInterface = $userInterface;
        }

        $this->userInterface = $userInterface;
    }

    /**
     * Gets the instance of FinanceSystem.
     *
     * This method returns the same instance of FinanceSystem for every call.
     * If $userInterface is provided, it sets the UserInterface for the
     * instance. If not provided, the UserInterface is not changed.
     *
     * @param  UserInterface|null $userInterface the UserInterface to set
     * @return FinanceSystem      the FinanceSystem instance
     */
    public static function getInstance(?UserInterface $userInterface = null): FinanceSystem
    {
        if (self::$instance === null) {
            self::$instance = new self($userInterface);
        }

        return self::$instance;
    }

    public function __clone(): void
    {
        throw new RuntimeException('Cloning is not allowed.');
    }

    /**
     * Executes the run method of the current UserInterface.
     *
     * This method delegates the execution to the run method of the
     * UserInterface implementation held by this FinanceSystem instance,
     * if a UserInterface is set.
     */
    public function run(): void
    {
        $this->userInterface?->run();
    }
}
