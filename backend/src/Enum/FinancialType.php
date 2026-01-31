<?php

declare(strict_types=1);

namespace App\Enum;

enum FinancialType: string
{
    case Income = 'income';
    case Expense = 'expense';
}
