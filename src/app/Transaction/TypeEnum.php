<?php

declare(strict_types=1);

namespace App\Transaction;

enum TypeEnum: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
}
