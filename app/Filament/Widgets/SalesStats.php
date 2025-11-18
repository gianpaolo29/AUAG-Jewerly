<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaction;

class SalesStats extends BaseWidget
{
    // 🔹 FIXED: non-static property
    protected ?string $heading = 'Sales KPIs';

    protected function getStats(): array
    {
        $today  = Transaction::whereDate('created_at', today())->withSum('items', 'line_total')->get()->sum('items_sum_line_total');
        $week   = Transaction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->withSum('items', 'line_total')->get()->sum('items_sum_line_total');
        $month  = Transaction::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->withSum('items', 'line_total')->get()->sum('items_sum_line_total');

        return [
            Stat::make('Today', '₱' . number_format($today, 2)),
            Stat::make('This Week', '₱' . number_format($week, 2)),
            Stat::make('This Month', '₱' . number_format($month, 2)),
        ];
    }
}
