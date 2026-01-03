<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\Pages\ManageExpenses;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ExpenseResource extends TransactionResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingDown;

    protected static ?string $navigationLabel = 'Expense';

    protected static function transactionType(): TransactionType
    {
        return TransactionType::Expense;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExpenses::route('/'),
        ];
    }
}
