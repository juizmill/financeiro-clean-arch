<?php

declare(strict_types=1);

namespace App\Transaction\Domain;

interface UserInterface
{
    public function run(): void;
}
