<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;   // ← use Repeater instead of Group
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Primary picture via relation (morphOne simulated with maxItems(1))
            Repeater::make('primaryPicture')
                ->relationship('primaryPicture')   // Product::primaryPicture() relation
                ->schema([
                    FileUpload::make('url')
                        ->image()
                        ->disk('public')
                        ->directory('products')
                        ->visibility('public')
                        ->required()
                        ->columnSpanFull(),

                    Toggle::make('is_primary')
                    ->default(true)
                    ->hidden()
                    ->dehydrated(true), // ← hidden fields aren’t saved unless dehydrated

                ])
                ->minItems(1)          // ensure a record exists
                ->maxItems(1)          // keep it “one-to-one”
                ->columns(1)
                ->columnSpanFull(),

            TextInput::make('name')->required(),

            Select::make('category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->preload()
                ->required()
                ->placeholder('Select a category'),

            Textarea::make('description')->default(null)->columnSpanFull(),

            TextInput::make('price')->numeric()->required(),
            TextInput::make('quantity')->numeric()->minValue(0)->default(0),
        ]);
    }
}
