<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    /**
     * Show only users with role = 'customer'
     */
    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->whereIn('role', ['Customer', 'Staff']);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(), 
        ];
    }
}
