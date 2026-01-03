<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\Pages\ManageTransfers;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class TransferResource extends TransactionResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Transfer';

    protected static function transactionType(): TransactionType
    {
        return TransactionType::Transfer;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTransfers::route('/'),
        ];
    }
}
