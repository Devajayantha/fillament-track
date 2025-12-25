<?php

namespace App\Filament\Widgets\Concerns;

use Carbon\Carbon;

trait HasDateRangeFilter
{
    /**
     * Returns an array of date range options for the filter dropdown.
     */
    protected function getFilters(): ?array
    {
        // Example: last 7 days, this month, last month, custom
        return [
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_year' => 'This Year',
            'this_middle_month' => 'This Middle Month',
        ];
    }

    /**
     * Returns the start and end date for the selected filter.
     */
    public function getDateRange(): array
    {
        $now = Carbon::now();
        $filter = $this->filter ?? 'this_month';
        switch ($filter) {
            case 'last_month':
                $start = $now->copy()->subMonthNoOverflow()->startOfMonth();
                $end = $now->copy()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'this_middle_month':
                $start = $now->copy()->day(20);
                $end = $now->copy()->addMonthNoOverflow()->day(19);
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }
        return [$start->toDateString(), $end->toDateString()];
    }
}
