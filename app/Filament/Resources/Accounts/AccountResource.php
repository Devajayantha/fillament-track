<?php

namespace App\Filament\Resources\Accounts;

use App\Enums\AccountType;
use App\Filament\Resources\Accounts\Pages\ManageAccounts;
use App\Filament\Resources\Accounts\RelationManagers\AccountUsersRelationManager;
use App\Models\Account;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->dehydrated(fn () => ! Auth::user()?->is_admin)
                    ->required(fn () => ! Auth::user()?->is_admin),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options(AccountType::labels())
                    ->default(AccountType::Cash->value)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $user = Auth::user();

                if (! $user || $user->is_admin) {
                    return;
                }

                $query->where(function (Builder $inner) use ($user): void {
                    $inner
                        ->whereNull('user_id')
                        ->orWhere('user_id', $user->id);
                });
            })
            ->columns([
                TextColumn::make('row_number')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(false),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): ?string => match (true) {
                            $state instanceof AccountType => AccountType::labels()[$state->value] ?? $state->value,
                            is_string($state) => AccountType::labels()[$state] ?? $state,
                            default => null,
                        }
                    ),
                TextColumn::make('user_accounts_count')
                    ->label('Assignments')
                    ->counts('userAccounts')
                    ->sortable()
                    ->visible(fn () => Auth::user()?->is_admin ?? false),
                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(AccountType::labels()),
                SelectFilter::make('scope')
                    ->label('View')
                    ->options([
                        'all' => 'All Accounts',
                        'master' => 'Master Only',
                    ])
                    ->default('all')
                    ->visible(fn () => Auth::user()?->is_admin ?? false)
                    ->query(function (Builder $query, array $data): Builder {
                        return ($data['value'] ?? null) === 'master'
                            ? $query->whereNull('user_id')
                            : $query;
                    }),
            ])
            ->recordActionsColumnLabel('Action')
            ->recordActionsPosition(RecordActionsPosition::AfterColumns)
            ->recordActionsAlignment('left')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        if (! Auth::user()?->is_admin) {
            return [];
        }

        return [
            AccountUsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAccounts::route('/'),
        ];
    }
}
