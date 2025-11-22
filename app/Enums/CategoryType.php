<?php

namespace App\Enums;

enum CategoryType: string
{
    case Expense = 'expense';
    case Income = 'income';

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
        ];
    }
}
