<?php

namespace App\Filament\Resources\Pawns\Pages;

use App\Filament\Resources\Pawns\PawnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPawns extends ListRecords
{
    protected static string $resource = PawnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
