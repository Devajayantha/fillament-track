<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\Pages\ManageTransactions;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\UserAccount;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

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
                Select::make('type')
                    ->options(TransactionType::labels())
                    ->required()
                    ->live()
                    ->disabled(fn (Get $get): bool => filled($get('type')))
                    ->visible(fn (Get $get): bool => ($get('type') ?? null) !== TransactionType::Transfer->value),
                Select::make('category_id')
                    ->label('Category')
                    ->options(fn (Get $get): array => static::categoryOptions(
                        static::resolveUserId($get('user_id')),
                        $get('type')
                    ))
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => ($get('type') ?? null) !== TransactionType::Transfer->value)
                    ->required(fn (Get $get): bool => ($get('type') ?? null) !== TransactionType::Transfer->value),
                Select::make('account_id')
                    ->label('Account')
                    ->options(fn (Get $get): array => static::userAccountOptions(static::resolveUserId($get('user_id'))))
                    ->visible(fn (Get $get): bool => ($get('type') ?? null) !== TransactionType::Income->value)
                    ->required(fn (Get $get): bool => in_array($get('type'), [
                        TransactionType::Expense->value,
                        TransactionType::Transfer->value,
                    ], true))
                    ->searchable(),
                Select::make('destination_account_id')
                    ->label(fn (Get $get): string => ($get('type') ?? null) === TransactionType::Income->value ? 'Account' : 'Destination account')
                    ->options(fn (Get $get): array => static::userAccountOptions(static::resolveUserId($get('user_id'))))
                    ->visible(fn (Get $get): bool => in_array($get('type'), [
                        TransactionType::Transfer->value,
                        TransactionType::Income->value,
                    ], true))
                    ->required(fn (Get $get): bool => in_array($get('type'), [
                        TransactionType::Transfer->value,
                        TransactionType::Income->value,
                    ], true))
                    ->searchable(),
                DateTimePicker::make('transaction_date')
                    ->label('Transaction date')
                    ->seconds(false)
                    ->default(now())
                    ->required(),
                TextInput::make('amount')
                    ->numeric()
                    ->minValue(0.01)
                    ->suffix('IDR')
                    ->step(0.01)
                    ->required(),
                Textarea::make('desc')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),
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
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('rowNumber')
                    ->label('#')
                    ->state(static function (TextColumn $column, $record, int $rowLoop): int {
                        return $rowLoop + 1;
                    })
                    ->sortable(false)
                    ->searchable(false),
                TextColumn::make('user.email')
                    ->label('User')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => match (true) {
                            $state instanceof TransactionType => TransactionType::labels()[$state->value] ?? $state->value,
                            is_string($state) => TransactionType::labels()[$state] ?? $state,
                            default => (string) ($state ?? 'N/A'),
                        }
                    )
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->placeholder('N/A')
                    ->toggleable(),
                TextColumn::make('account.account.name')
                    ->label('Account')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('destinationAccount.account.name')
                    ->label('Destination')
                    ->placeholder('N/A')
                    ->toggleable(),
                TextColumn::make('transaction_date')
                    ->label('Transaction date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(TransactionType::labels()),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $inner, string $date): Builder => $inner->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $inner, string $date): Builder => $inner->whereDate('created_at', '<=', $date));
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTransactions::route('/'),
        ];
    }

    protected static function resolveUserId(?int $selectedUserId): ?int
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->is_admin) {
            return $selectedUserId;
        }

        return $user->id;
    }

    /**
     * @return array<int, string>
     */
    protected static function userAccountOptions(?int $userId): array
    {
        if (! $userId) {
            return [];
        }

        return UserAccount::query()
            ->with('account')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (UserAccount $assignment): array => [
                $assignment->id => $assignment->account?->name ?? "Account #{$assignment->id}",
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected static function categoryOptions(?int $userId, ?string $type): array
    {
        if (! $type) {
            return [];
        }

        return Category::query()
            ->where('type', $type)
            ->when($userId, function (Builder $query, int $id): Builder {
                return $query->where(function (Builder $inner) use ($id): void {
                    $inner
                        ->whereNull('user_id')
                        ->orWhere('user_id', $id);
                });
            }, fn (Builder $query): Builder => $query->whereNull('user_id'))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
