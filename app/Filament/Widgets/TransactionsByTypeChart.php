<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionsByTypeChart extends ChartWidget
{
    protected ?string $heading = 'Transactions by Type (Last 30 Days)';

    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        $from = now()->subDays(30);

        $buy    = Transaction::where('type', 'Buy')->where('created_at', '>=', $from)->count();
        $pawn   = Transaction::where('type', 'Pawn')->where('created_at', '>=', $from)->count();
        $repair = Transaction::where('type', 'Repair')->where('created_at', '>=', $from)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data'  => [$buy, $pawn, $repair],
                ],
            ],
            'labels' => ['Buy', 'Pawn', 'Repair'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
