<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    // ChartWidget uses NON-static heading in v3
    protected ?string $heading = 'Revenue (Last 30 Days)';

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        // Pull transactions from last 30 days and sum line_total per day
        $rows = Transaction::query()
            ->where('created_at', '>=', $start)
            ->withSum('items', 'line_total')
            ->get()
            ->groupBy(fn ($t) => $t->created_at->toDateString())
            ->map(fn ($g) => $g->sum('items_sum_line_total'));

        $labels = [];
        $data   = [];

        // ✅ fix: add the missing $ in the loop condition
        for ($i = 0; $i < 30; $i++) {
            $day = now()->subDays(29 - $i)->toDateString();
            $labels[] = $day;
            $data[]   = (float) ($rows[$day] ?? 0);
            // or: $data[] = (float) ($rows->get($day, 0));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data'  => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
