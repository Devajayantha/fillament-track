<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\TransferResource;

class ManageTransfers extends ManageTransactionRecords
{
    protected static string $resource = TransferResource::class;

    protected static function getTransactionType(): TransactionType
    {
        return TransactionType::Transfer;
    }

    protected function getCreateActionLabel(): string
    {
        return 'Add Transfer';
    }

    protected function getCreateActionIcon(): string
    {
        return 'heroicon-m-arrows-right-left';
    }

    protected function getCreateActionHeading(): string
    {
        return 'Add Transfer Transaction';
    }
}
