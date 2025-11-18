<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class RecentTransactions extends BaseWidget
{
    // ✅ must be static for TableWidget
    protected static ?string $heading = '🧾 Recent Transactions';
    protected static ?int $sort = 4;
        protected static string $maxWidth = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->latest()
                    ->with(['items.product'])
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),

                TextColumn::make('items')
                    ->label('Items')
                    ->getStateUsing(function ($record) {
                        return $record->items
                            ->pluck('product.name')
                            ->filter()
                            ->implode(', ');
                    })
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
