<?php

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\UserAccount;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

abstract class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    abstract protected static function transactionType(): TransactionType;

    protected static function transactionTypeValue(): string
    {
        return static::transactionType()->value;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
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
                Hidden::make('type')
                    ->default(static::transactionTypeValue())
                    ->dehydrated(),
                Select::make('category_id')
                    ->label('Category')
                    ->options(fn (Get $get): array => static::categoryOptions(
                        static::resolveUserId($get('user_id')),
                        $get('type') ?? static::transactionTypeValue()
                    ))
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => ($get('type') ?? static::transactionTypeValue()) !== TransactionType::Transfer->value)
                    ->required(fn (Get $get): bool => ($get('type') ?? static::transactionTypeValue()) !== TransactionType::Transfer->value),
                Select::make('account_id')
                    ->label('Account')
                    ->options(function (Get $get): array {
                        $options = static::userAccountOptions(static::resolveUserId($get('user_id')));

                        if (
                            ($get('type') ?? static::transactionTypeValue()) === TransactionType::Transfer->value
                            && ($destinationId = $get('destination_account_id'))
                        ) {
                            unset($options[$destinationId]);
                        }

                        return $options;
                    })
                    ->visible(fn (Get $get): bool => ($get('type') ?? static::transactionTypeValue()) !== TransactionType::Income->value)
                    ->required(fn (Get $get): bool => in_array(($get('type') ?? static::transactionTypeValue()), [
                        TransactionType::Expense->value,
                        TransactionType::Transfer->value,
                    ], true))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?int $state, Get $get): void {
                        if (
                            ($get('type') ?? static::transactionTypeValue()) === TransactionType::Transfer->value
                            && $state === $get('destination_account_id')
                        ) {
                            $set('destination_account_id', null);
                        }
                    }),
                Select::make('destination_account_id')
                    ->label(fn (Get $get): string => ($get('type') ?? static::transactionTypeValue()) === TransactionType::Income->value ? 'Account' : 'Destination account')
                    ->options(function (Get $get): array {
                        $options = static::userAccountOptions(static::resolveUserId($get('user_id')));

                        if (
                            ($get('type') ?? static::transactionTypeValue()) === TransactionType::Transfer->value
                            && ($sourceAccountId = $get('account_id'))
                        ) {
                            unset($options[$sourceAccountId]);
                        }

                        return $options;
                    })
                    ->visible(fn (Get $get): bool => in_array(($get('type') ?? static::transactionTypeValue()), [
                        TransactionType::Transfer->value,
                        TransactionType::Income->value,
                    ], true))
                    ->required(fn (Get $get): bool => in_array(($get('type') ?? static::transactionTypeValue()), [
                        TransactionType::Transfer->value,
                        TransactionType::Income->value,
                    ], true))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?int $state, Get $get): void {
                        if ($state === $get('account_id')) {
                            $set('account_id', null);
                        }
                    })
                    ->rule(fn (Get $get) => ($get('type') ?? static::transactionTypeValue()) === TransactionType::Transfer->value ? 'different:account_id' : null),
                DatePicker::make('transaction_date')
                    ->label('Transaction date')
                    ->default(fn (): string => now()->format('Y-m-d'))
                    ->native(false)
                    ->afterStateHydrated(function (DatePicker $component, $state): void {
                        if ($state) {
                            return;
                        }

                        $component->state(now()->format('Y-m-d'));
                    })
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

                $query->where('type', static::transactionTypeValue());
            })
            ->defaultSort('transaction_date', 'desc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('rowNumber')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(false)
                    ->searchable(false),
                TextColumn::make('user.email')
                    ->label('User')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->toggleable()
                    ->visible(static::transactionTypeValue() === TransactionType::Transfer->value),
                TextColumn::make('transaction_date')
                    ->label('Transaction date')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                    TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                    TextColumn::make('desc')
                    ->label('Description')
                    ->toggleable()
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->filters([
                Filter::make('transaction_date')
                    ->label('Transaction date')
                    ->schema([
                        DatePicker::make('from')
                            ->default(fn (): string => now()->day(19)->format('Y-m-d')),
                        DatePicker::make('until')
                            ->default(fn (): string => now()->subMonthNoOverflow()->day(20)->format('Y-m-d')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $inner, string $date): Builder => $inner->whereDate('transaction_date', '<=', $date))
                            ->when($data['until'] ?? null, fn (Builder $inner, string $date): Builder => $inner->whereDate('transaction_date', '>=', $date));
                    })
                    ->default(fn (): array => [
                        'from' => now()->startOfMonth()->format('Y-m-d'),
                        'until' => now()->endOfMonth()->format('Y-m-d'),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('From ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Until ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
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
