<?php

namespace App\Filament\Resources\Repairs\Schemas;

use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;

class RepairForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Customer')
                ->relationship('customer', 'name')
                ->options(User::where('role', 'customer')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('price')
                ->required()
                ->numeric()
                ->default(0.0)
                ->prefix('₱'),

            Textarea::make('description')
                ->default(null)
                ->columnSpanFull(),

            Select::make('status')
                ->options([
                    'pending'     => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed'   => 'Completed',
                    'cancelled'   => 'Cancelled',
                ])
                ->default('pending')
                ->required(),

            Repeater::make('images')
            ->label('Repair Images')
            ->relationship('images')
            ->schema([
                FileUpload::make('url')
                    ->label('Image')
                    ->image()
                    ->directory('repairs')
                    ->disk('public')
                    ->openable()
                    ->downloadable(),
            ])
            ->maxItems(1) // ⛔ limits to one
            ->addActionLabel(false) // ⛔ hides “Add” button
             ->hidden(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord)
                ]);
    }
}
