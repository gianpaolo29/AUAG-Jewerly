<?php

namespace App\Filament\Resources\Pawns\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon;

class PawnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // --- Customer (text inputs only) ---
            TextInput::make('customer_name')->label('Customer Name')->required()->maxLength(120),
            TextInput::make('customer_mobile')
                ->label('Mobile Number')
                ->required()
                ->maxLength(13)
                ->placeholder('09XXXXXXXXX or 9XXXXXXXXX')
                ->helperText('Accepts 09XXXXXXXXX or 9XXXXXXXXX (PH mobile only)')
                ->mask('99999999999') // keeps input numeric and up to 11 digits
                ->rule('regex:/^(09\d{9}|9\d{9})$/') // ✅ allows 09xxxxxxxxx or 9xxxxxxxxx
                ->validationMessages([
                    'regex' => 'Enter a valid PH mobile number (09XXXXXXXXX or 9XXXXXXXXX).',
                    'max' => 'Mobile number must be 10 or 11 digits long.',
                ]),

            // --- Pawn details ---
            TextInput::make('title')->label('Item Title')->required()->maxLength(255),
            Textarea::make('description')->label('Item Description')->rows(3),

            TextInput::make('price')
                ->label('Principal / Appraised Value (₱)')
                ->numeric()->prefix('₱')->required()->default(0),

            TextInput::make('interest_cost') // your base interest (if any)
                ->label('Base Interest (₱)')
                ->numeric()->prefix('₱')->default(0),

            // Due date: auto 3 months from today, shown but not editable
            DatePicker::make('due_date')
                ->label('Due Date')
                ->default(fn () => Carbon::now()->addMonthsNoOverflow(3))
                ->disabled()
                ->helperText('Auto-set: 3 months from today'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'active'    => 'Active',
                    'redeemed'  => 'Redeemed',
                    'forfeited' => 'Forfeited',
                ])
                ->default('active')->required(),
        ]);
    }
}
