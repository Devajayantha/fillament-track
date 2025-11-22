<?php

namespace App\Filament\Resources\UserAccounts;

use App\Filament\Resources\UserAccounts\Pages\ManageUserAccounts;
use App\Models\Account;
use App\Models\UserAccount;
use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserAccountResource extends Resource
{
    protected static ?string $model = UserAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user !== null && ! $user->is_admin;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->dehydrated(fn () => ! Auth::user()?->is_admin),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Auth::user()?->is_admin)
                    ->required(fn () => Auth::user()?->is_admin)
                    ->live(),
                Select::make('account_id')
                    ->label('Account')
                    ->options(fn (Get $get): array => static::availableAccountOptions(
                        static::resolveTargetUserId($get('user_id')),
                        $get('account_id')
                    ))
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get): bool => ! static::resolveTargetUserId($get('user_id')))
                    ->required(),
                TextInput::make('initial_balance')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                if (! Auth::user()?->is_admin) {
                    $query->where('user_id', Auth::id());
                }
            })
            ->columns([
                TextColumn::make('account.name')
                    ->label('Account')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('initial_balance')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('balance')
                    ->numeric(decimalPlaces: 2)
                    ->label('Balance')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('account_id')
                    ->label('Account')
                    ->relationship(
                        'account',
                        'name',
                        function (Builder $query): Builder {
                            $user = Auth::user();

                            if (! $user || $user->is_admin) {
                                return $query;
                            }

                            return $query->where(function (Builder $inner) use ($user): void {
                                $inner
                                    ->whereNull('user_id')
                                    ->orWhere('user_id', $user->id);
                            });
                        }
                    )
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUserAccounts::route('/'),
        ];
    }

    protected static function resolveTargetUserId(?int $selectedUserId): ?int
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return $user->is_admin ? $selectedUserId : $user->id;
    }

    /**
     * @return array<int, string>
     */
    protected static function availableAccountOptions(?int $userId, ?int $currentAccountId): array
    {
        if (! $userId) {
            return [];
        }

        $assignedAccountIds = UserAccount::query()
            ->where('user_id', $userId)
            ->when($currentAccountId, fn (Builder $query, int $accountId): Builder => $query->where('account_id', '!=', $accountId))
            ->pluck('account_id')
            ->all();

        return Account::query()
            ->where(function (Builder $query) use ($userId): void {
                $query
                    ->whereNull('user_id')
                    ->orWhere('user_id', $userId);
            })
            ->when($assignedAccountIds !== [], fn (Builder $query): Builder => $query->whereNotIn('id', $assignedAccountIds))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
