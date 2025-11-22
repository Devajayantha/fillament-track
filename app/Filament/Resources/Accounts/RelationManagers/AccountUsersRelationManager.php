<?php

namespace App\Filament\Resources\Accounts\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountUsersRelationManager extends RelationManager
{
    protected static string $relationship = 'userAccounts';

    protected static ?string $recordTitleAttribute = 'user.name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('initial_balance')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                Toggle::make('is_primary')
                    ->label('Primary account'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('Unassigned')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('initial_balance')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('balance')
                    ->numeric(decimalPlaces: 2)
                    ->label('Balance')
                    ->sortable(),
                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
