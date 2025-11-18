<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected static bool $shouldRestoreFormState = false; 
    protected function getFormDefaults(): array
    {
        return [
            'name' => '',
            'email' => '',
            'password' => '',
            'role' => 'staff',
        ];
    }
}
