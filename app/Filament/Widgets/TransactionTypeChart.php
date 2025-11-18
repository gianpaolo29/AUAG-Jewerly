<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionsTypeChart extends ChartWidget
{
    protected?string $heading = 'Transactions by Type';

    protected function getData(): array
    {
        $counts = Transaction::query()
            ->selectRaw('type, COUNT(*) as c')
            ->groupBy('type')
            ->pluck('c', 'type')
            ->all();

        $types = ['buy','sell','pawn','repair'];
        $labels = array_map('ucfirst', $types);
        $data = array_map(fn($t) => (int) ($counts[$t] ?? 0), $types);

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
