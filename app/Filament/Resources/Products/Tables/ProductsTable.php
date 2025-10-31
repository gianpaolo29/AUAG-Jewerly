<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;     // ← add
use Illuminate\Support\Facades\Storage;      // ← add
use Illuminate\Support\Str;
class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumb')
                ->label('Image')
                ->getStateUsing(function ($record) {
                    // Prefer primary, else first picture
                    $path = data_get($record, 'primaryPicture.url')
                        ?? data_get($record, 'pictures.0.url');

                    if (! $path) {
                        return null; // will fall back to defaultImageUrl
                    }

                    // If already a full URL (CDN/S3/http), return it as is
                    if (Str::startsWith($path, ['http://', 'https://'])) {
                        return $path;
                    }

                    // If someone saved "storage/..." in DB, normalize to relative
                    $path = ltrim(preg_replace('#^storage/#', '', $path), '/');

                    // Convert relative storage path to a public URL
                    return Storage::disk('public')->url($path);
                })
                ->size(56)            // or ->height(56) / ->square()
                ->extraImgAttributes(['loading' => 'lazy'])
                ->defaultImageUrl('https://placehold.co/112x112?text=No+Image'),
                    

                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('PHP'),

                TextColumn::make('quantity')
                    ->label('Quantity'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                Filter::make('price')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_price')->numeric()->label('Min Price'),
                        \Filament\Forms\Components\TextInput::make('max_price')->numeric()->label('Max Price'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_price'] ?? null, fn ($q, $min) => $q->where('price', '>=', $min))
                            ->when($data['max_price'] ?? null, fn ($q, $max) => $q->where('price', '<=', $max));
                    }),

                Filter::make('quantity')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_quantity')->numeric()->label('Min Quantity'),
                        \Filament\Forms\Components\TextInput::make('max_quantity')->numeric()->label('Max Quantity'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_quantity'] ?? null, fn ($q, $min) => $q->where('quantity', '>=', $min))
                            ->when($data['max_quantity'] ?? null, fn ($q, $max) => $q->where('quantity', '<=', $max));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
