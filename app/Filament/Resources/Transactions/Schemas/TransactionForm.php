<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Models\Product;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            
            Select::make('type')
                ->label('Type')
                ->options(['buy' => 'buy'])
                ->default('buy')
                ->disabled()
                ->dehydrated(true),

            // 🔹 Items Repeater
            Repeater::make('items')
                ->relationship()
                ->defaultItems(1)
                ->columns(6)
                ->live()
                ->schema([
                    Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(3)
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            // $state = selected product_id
                            $product = Product::query()->select('price','quantity')->find($state);
                            $price   = (float) ($product->price ?? 0);
                            $qty     = max((int) ($get('quantity') ?? 1), 1);

                            $set('unit_price', $price);
                            $set('line_total', $qty * $price);
                        }),

                    TextInput::make('quantity')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->live()
                        // 👇 helper shows current stock
                        ->helperText(function ($get) {
                            $pid = $get('product_id');
                            if (!$pid) return null;
                            $stock = (int) (Product::query()->whereKey($pid)->value('quantity') ?? 0);
                            return "In stock: {$stock}";
                        })
                        // 👇 server-side rule blocks save when qty > stock
                        ->rule(function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $pid = $get('product_id');
                                if (!$pid) return;
                                $stock = (int) (Product::query()->whereKey($pid)->value('quantity') ?? 0);
                                if ((int) $value > $stock) {
                                    $fail("Only {$stock} left in stock.");
                                }
                            };
                        })
                        // 👇 live feedback (notification) if qty > stock
                        ->afterStateUpdated(function ($set, $get) {
                            $pid   = $get('product_id');
                            $qty   = max((int) ($get('quantity') ?? 1), 1);
                            $price = (float) ($get('unit_price') ?? 0);

                            if ($pid) {
                                $stock = (int) (Product::query()->whereKey($pid)->value('quantity') ?? 0);
                                if ($qty > $stock) {
                                    Notification::make()
                                        ->title('Not enough stock')
                                        ->body("Only {$stock} in stock.")
                                        ->warning()
                                        ->send();
                                }
                            }

                            $set('line_total', $qty * $price);
                        }),

                    TextInput::make('unit_price')
                        ->label('Unit Price')
                        ->numeric()
                        ->prefix('₱')
                        ->readOnly()
                        ->default(0),

                    TextInput::make('line_total')
                        ->label('Line Total')
                        ->prefix('₱')
                        ->readOnly()
                        ->dehydrated(true),
                ])
                ->afterStateUpdated(function ($set, $get) {
                    $subtotal = collect($get('items') ?? [])->sum('line_total');
                    $discount = (float) ($get('discount') ?? 0);
                    $set('subtotal', $subtotal);
                    $set('total', max($subtotal - $discount, 0));
                }),

            // 🔹 Summary Fields
            TextInput::make('subtotal')
                ->label('Subtotal')
                ->prefix('₱')
                ->readOnly()
                ->default(0),

            TextInput::make('discount')
                ->label('Discount')
                ->prefix('₱')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->live()
                ->afterStateUpdated(function ($set, $get) {
                    $set('total', max((float) $get('subtotal') - (float) $get('discount'), 0));
                }),

            TextInput::make('total')
                ->label('Total (Cash)')
                ->prefix('₱')
                ->readOnly()
                ->default(0),
        ]);
    }
}
