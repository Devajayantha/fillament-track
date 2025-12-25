<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use Filament\Widgets\ChartWidget;
use App\Filament\Widgets\Concerns\HasDateRangeFilter;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Auth;

class ExpenseByCategoryChart extends ChartWidget
{
    use HasDateRangeFilter;

    protected ?string $heading = 'Expenses by Category';

    protected function getData(): array
    {
        [$start, $end] = $this->getDateRange();

        $data = Transaction::query()
            ->with('category:id,name')
            ->where('type', TransactionType::Expense)
            ->where('user_id', Auth::id())
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get()
            ->mapWithKeys(fn($row) => [
                $row->category->name => (float) $row->total
            ]);

        $labels = $data->keys()->toArray();
        $totals = $data->values()->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $totals,
                    'datalabels' => array_map(fn($v) => number_format($v, 2), $totals),
                ],
            ],
            'labels' => $labels,
        ];
    }


    protected function getType(): string
    {
        return 'bar';
    }


    protected function getOptions(): ?array
    {
        return [
            'indexAxis' => 'y', // This makes the bar chart horizontal (width-based)
            'plugins' => [
                'datalabels' => [
                    'anchor' => 'end',
                    'align' => 'right',
                    'color' => '#fff',
                    'font' => [
                        'weight' => 'bold',
                        'size' => 14,
                    ],
                    'formatter' => \Filament\Support\RawJs::make('value => value.toLocaleString()'),
                ],
            ],
        ];
    }

    public function getColumnSpan(): int|string|array
    {
        return 2;
    }

    protected function getPlugins(): array
    {
        return [
            'ChartDataLabels',
        ];
    }
}
