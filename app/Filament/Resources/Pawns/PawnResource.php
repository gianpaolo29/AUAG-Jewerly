<?php

namespace App\Filament\Resources\Pawns;

use App\Filament\Resources\Pawns\Pages\CreatePawn;
use App\Filament\Resources\Pawns\Pages\EditPawn;
use App\Filament\Resources\Pawns\Pages\ListPawns;
use App\Filament\Resources\Pawns\Schemas\PawnForm;
use App\Filament\Resources\Pawns\Tables\PawnsTable;
use App\Models\Pawn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PawnResource extends Resource
{
    protected static ?string $model = Pawn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

      
    protected static string|\UnitEnum|null $navigationGroup = 'Management';

    protected static ?string $recordTitleAttribute = 'Pawn';


    public static function form(Schema $schema): Schema
    {
        return PawnForm::configure($schema);
    }
    
    public static function table(Table $table): Table
    {
        return PawnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPawns::route('/'),
            'create' => CreatePawn::route('/create'),
            'edit' => EditPawn::route('/{record}/edit'),
        ];
    }
}
