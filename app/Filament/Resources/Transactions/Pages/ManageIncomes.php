<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\IncomeResource;

class ManageIncomes extends ManageTransactionRecords
{
    protected static string $resource = IncomeResource::class;

    protected static function getTransactionType(): TransactionType
    {
        return TransactionType::Income;
    }

    protected function getCreateActionLabel(): string
    {
        return 'Add Income';
    }

    protected function getCreateActionIcon(): string
    {
        return 'heroicon-m-banknotes';
    }

    protected function getCreateActionHeading(): string
    {
        return 'Add Income Transaction';
    }
}
