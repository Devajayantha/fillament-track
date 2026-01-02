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
            'this_middle_month' => 'This Middle Month',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_year' => 'This Year',
        ];
    }

    /**
     * Returns the start and end date for the selected filter.
     */
    public function getDateRange(): array
    {
        $now = Carbon::now();
        $filter = $this->filter ?? 'this_middle_month';

        switch ($filter) {
            case 'last_month':
                $start = $now->copy()->subMonthNoOverflow()->startOfMonth();
                $end = $now->copy()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'this_middle_month':
            default:
                $start = $now->copy()->day(19);
                $end = $now->copy()->subMonthNoOverflow()->day(20);
                break;
        }

        return [$start, $end];
    }
}
