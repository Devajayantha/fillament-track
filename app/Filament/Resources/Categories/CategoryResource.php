<?php

namespace App\Filament\Resources\Categories;

use App\Enums\CategoryType;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

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
                    ->dehydrated(fn () => ! Auth::user()?->is_admin),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options(CategoryType::labels())
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
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string => match (true) {
                            $state instanceof CategoryType => CategoryType::labels()[$state->value] ?? $state->value,
                            is_string($state) => CategoryType::labels()[$state] ?? $state,
                            default => (string) ($state ?? 'N/A'),
                        }
                    )
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->placeholder('')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(CategoryType::labels()),
                SelectFilter::make('scope')
                    ->label('View')
                    ->options([
                        'all' => 'All Categories',
                        'master' => 'Master Only',
                    ])
                    ->default('all')
                    ->visible(fn () => Auth::user()?->is_admin ?? false)
                    ->query(function (Builder $query, array $data): Builder {
                        return ($data['value'] ?? null) === 'master'
                            ? $query->whereNull('user_id')
                            : $query;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
