<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class LowStock extends BaseWidget
{
    // TableWidget uses a static heading in v3
    protected static ?string $heading = '⚠️ Low Stock Products';
    protected static ?int $sort = 3; // optional ordering on dashboard

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('quantity', '<=', 5)
                    ->orderBy('quantity', 'asc')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (int $state) => $state <= 2 ? 'danger' : 'warning')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Unit Price')
                    ->money('PHP', true)
                    ->sortable(),
            ])
            ->paginated(true);
    }
}
