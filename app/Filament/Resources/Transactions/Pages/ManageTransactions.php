<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactions extends ManageRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('createIncome')
                ->label('Add Income')
                ->icon('heroicon-m-banknotes')
                ->modalHeading('Add Income Transaction')
                ->fillForm(fn (array $data): array => array_merge($data, [
                    'type' => TransactionType::Income->value,
                ]))
                ->mutateDataUsing(fn (array $data): array => array_merge($data, [
                    'type' => TransactionType::Income->value,
                ])),
            CreateAction::make('createExpense')
                ->label('Add Expense')
                ->icon('heroicon-m-arrow-trending-down')
                ->modalHeading('Add Expense Transaction')
                ->fillForm(fn (array $data): array => array_merge($data, [
                    'type' => TransactionType::Expense->value,
                ]))
                ->mutateDataUsing(fn (array $data): array => array_merge($data, [
                    'type' => TransactionType::Expense->value,
                ])),
            CreateAction::make('createTransfer')
                ->label('Add Transfer')
                ->icon('heroicon-m-arrows-right-left')
                ->modalHeading('Add Transfer Transaction')
                ->fillForm(fn (array $data): array => array_merge($data, [
                    'type' => TransactionType::Transfer->value,
                ]))
                ->mutateDataUsing(fn (array $data): array => array_merge($data, [
                    'type' => TransactionType::Transfer->value,
                ])),
        ];
    }
}
