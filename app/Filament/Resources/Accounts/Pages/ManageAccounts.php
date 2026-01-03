<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManageAccounts extends ManageRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            $this->syncBalancesAction(),
        ];
    }

    protected function syncBalancesAction(): Action
    {
        return Action::make('syncBalances')
            ->label('Sync Account Balances')
            ->icon('heroicon-m-arrow-path')
            ->requiresConfirmation()
            ->modalHeading('Recalculate balances from transactions')
            ->action(function (): void {
                $this->syncBalances();
            });
    }

    protected function syncBalances(): void
    {
        $userId = Auth::user()?->is_admin ? null : Auth::id();
        $updated = Transaction::syncAccountBalances($userId);

        Notification::make()
            ->title('Balances synced')
            ->body("Updated {$updated} account balances.")
            ->success()
            ->send();
    }
}
