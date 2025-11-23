<?php

namespace App\Enums;

enum AccountType: string
{
    case Cash = 'cash';
    case Ewallet = 'ewallet';
    case Bank = 'bank';

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
            self::Cash->value => 'Cash',
            self::Ewallet->value => 'E-Wallet',
            self::Bank->value => 'Bank',
        ];
    }
}
