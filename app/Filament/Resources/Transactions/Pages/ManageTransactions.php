<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Transactions\Widgets\TransactionSummary;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

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
            Action::make('syncBalances')
                ->label('Sync Account Balances')
                ->icon('heroicon-m-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Recalculate balances from transactions')
                ->action(function (): void {
                    $userId = Auth::user()?->is_admin ? null : Auth::id();
                    $updated = Transaction::syncAccountBalances($userId);

                    Notification::make()
                        ->title('Balances synced')
                        ->body("Updated {$updated} account balances.")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionSummary::class,
        ];
    }
}
