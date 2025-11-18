<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
// Removed unused Toggle import: use Filament\Forms\Components\Toggle;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Repeater::make('primaryPicture')
                ->relationship('primaryPicture')
                ->schema([
                    FileUpload::make('url') 
                        ->image()
                        ->disk('public')
                        ->directory('products')
                        ->visibility('public')
                        ->required()
                        ->columnSpanFull(), // Removed the comma here!
                    
                    // The Toggle component was removed from here.

                ])
                ->minItems(1) 
                ->maxItems(1) 
                ->deletable(false)
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

            TextInput::make('price')
                ->numeric()
                ->required()
                ->minValue(0.01),
                
            TextInput::make('quantity')->numeric()->minValue(0)->default(0),
        ]);
    }
}