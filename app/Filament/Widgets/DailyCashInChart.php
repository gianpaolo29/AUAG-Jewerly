<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DailyCashInChart extends ChartWidget
{
    protected ?string $heading = 'Daily Cash-In (Last 7 Days)';

    // full width row
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $data   = [];

        $today = now()->startOfDay();

        for ($i = 6; $i >= 0; $i--) {
            $day = (clone $today)->subDays($i);
            $labels[] = $day->format('M d');

            $sum = Transaction::whereDate('created_at', $day)
                ->withSum('items', 'line_total')
                ->get()
                ->sum('items_sum_line_total');
                

            $data[] = (float) $sum;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cash-In (₱)',
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
