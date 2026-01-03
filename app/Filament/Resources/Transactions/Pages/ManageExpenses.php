<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\ExpenseResource;

class ManageExpenses extends ManageTransactionRecords
{
    protected static string $resource = ExpenseResource::class;

    protected static function getTransactionType(): TransactionType
    {
        return TransactionType::Expense;
    }

    protected function getCreateActionLabel(): string
    {
        return 'Add Expense';
    }

    protected function getCreateActionIcon(): string
    {
        return 'heroicon-m-arrow-trending-down';
    }

    protected function getCreateActionHeading(): string
    {
        return 'Add Expense Transaction';
    }
}
