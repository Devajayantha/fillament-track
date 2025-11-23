<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TransactionSummary extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $now = now();
        $today = $now->copy();
        $monthStart = $now->copy()->startOfMonth();
        $monthLabel = $now->format('F Y');

        return [
            $this->statForDay('Today Income', $today, TransactionType::Income),
            $this->statForDay('Today Expense', $today, TransactionType::Expense),
            $this->statForDay('Today Transfer', $today, TransactionType::Transfer),
            $this->statForRange('This Month Income', $monthStart, $now, TransactionType::Income, $monthLabel),
            $this->statForRange('This Month Expense', $monthStart, $now, TransactionType::Expense, $monthLabel),
            $this->statForRange('This Month Transfer', $monthStart, $now, TransactionType::Transfer, $monthLabel),
        ];
    }

    protected function statForDay(string $label, Carbon $date, TransactionType $type): Stat
    {
        $amount = $this->amountForRange($date, $date, $type);
        $formatted = 'IDR ' . number_format($amount, 2);

        $color = match ($type) {
            TransactionType::Income => 'success',
            TransactionType::Expense => 'danger',
            default => 'warning',
        };

        return Stat::make($label, $formatted)
            ->description($date->toDateString())
            ->color($color);
    }

    protected function statForRange(string $label, Carbon $from, Carbon $to, TransactionType $type, string $description): Stat
    {
        $amount = $this->amountForRange($from, $to, $type);
        $formatted = 'IDR ' . number_format($amount, 2);

        $color = match ($type) {
            TransactionType::Income => 'success',
            TransactionType::Expense => 'danger',
            default => 'warning',
        };

        return Stat::make($label, $formatted)
            ->description($description)
            ->color($color);
    }

    protected function amountForRange(Carbon $from, Carbon $to, TransactionType $type): float
    {
        $user = Auth::user();

        return (float) Transaction::query()
            ->when(! $user?->is_admin, fn ($query) => $query->where('user_id', $user?->id))
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->where('type', $type->value)
            ->sum('amount');
    }
}
