<?php

namespace App\Filament\Resources\UserAccounts\Pages;

use App\Filament\Resources\UserAccounts\UserAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUserAccounts extends ManageRecords
{
    protected static string $resource = UserAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
