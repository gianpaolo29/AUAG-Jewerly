<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(120)
                ->autocomplete('off'), // disable autofill

            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->autocomplete('off'), // disable autofill

            // Hide password on edit; visible & required on create
            TextInput::make('password')
                ->password()
                ->revealable()
                ->autocomplete('new-password')
                ->rule('min:8')
                ->required(fn (string $operation) => $operation === 'create')
                ->hidden(fn (string $operation) => $operation === 'edit'),
        ]);
    }
}
