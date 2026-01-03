<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\Pages\ManageIncomes;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class IncomeResource extends TransactionResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Income';

    protected static function transactionType(): TransactionType
    {
        return TransactionType::Income;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageIncomes::route('/'),
        ];
    }
}
