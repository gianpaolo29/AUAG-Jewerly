<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\SiteResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

}