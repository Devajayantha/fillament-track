<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\TransactionType;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

abstract class ManageTransactionRecords extends ManageRecords
{
    abstract protected static function getTransactionType(): TransactionType;

    abstract protected function getCreateActionLabel(): string;

    abstract protected function getCreateActionIcon(): string;

    abstract protected function getCreateActionHeading(): string;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('create')
                ->label($this->getCreateActionLabel())
                ->icon($this->getCreateActionIcon())
                ->modalHeading($this->getCreateActionHeading())
                ->fillForm(fn (array $data): array => array_merge($data, [
                    'type' => static::getTransactionType()->value,
                ]))
                ->mutateDataUsing(fn (array $data): array => array_merge($data, [
                    'type' => static::getTransactionType()->value,
                ])),
        ];
    }
}
