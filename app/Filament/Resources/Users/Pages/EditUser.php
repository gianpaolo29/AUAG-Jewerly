<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Guard against any role outside ["user","staff"] on save.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $allowed = ['user', 'staff'];
        if (! in_array($data['role'] ?? 'user', $allowed, true)) {
            $data['role'] = 'user';
        }

        return $data;
    }
}
