<?php

namespace App\Enums;

enum TransactionType: string
{
    case Expense = 'expense';
    case Income = 'income';
    case Transfer = 'transfer';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Expense->value => 'Expense',
            self::Income->value => 'Income',
            self::Transfer->value => 'Transfer',
        ];
    }
}
