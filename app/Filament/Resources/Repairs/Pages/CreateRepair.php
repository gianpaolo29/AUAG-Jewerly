<?php

namespace App\Filament\Resources\Repairs\Pages;

use App\Filament\Resources\Repairs\RepairResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRepair extends CreateRecord
{
    protected static string $resource = RepairResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $customer = \App\Models\Customer::firstOrCreate(
            ['mobile_number' => $data['customer_mobile']],
            ['name' => $data['customer_name']]
        );

        $data['customer_id'] = $customer->id;

        unset($data['customer_name'], $data['customer_mobile']);
        return $data;
    }
}
