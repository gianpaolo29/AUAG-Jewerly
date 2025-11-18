<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class TopProducts extends BaseWidget
{
    // ✅ Must be static for TableWidget
    protected static ?string $heading = '🏆 Top Products';
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->orderByDesc('quantity') // or orderBy('sales_count') if you track that
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('In Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state <= 5 ? 'warning' : 'success'),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('PHP', true)
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
