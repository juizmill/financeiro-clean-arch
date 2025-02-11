<?php

declare(strict_types=1);

namespace App\Transaction\Domain;

enum TypeEnum: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
}
