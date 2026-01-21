<?php

namespace App\Enums;

enum TransactionType
{
    case INCOME;
    case EXPENSE;

    public function label(): string
    {
        return match($this) {
            TransactionType::INCOME => 'Income',
            TransactionType::EXPENSE => 'Expense',
        };
    }
}
