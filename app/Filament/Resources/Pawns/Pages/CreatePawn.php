<?php

namespace App\Filament\Resources\Pawns\Pages;

use App\Filament\Resources\Pawns\PawnResource;
use App\Models\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;

class CreatePawn extends CreateRecord
{
    protected static string $resource = PawnResource::class;

    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Find or create the customer
            $customer = Customer::firstOrCreate(
                ['mobile_number' => $data['customer_mobile']],
                ['name' => $data['customer_name']]
            );

            // Always set due_date to 3 months from today
            $data['due_date'] = Carbon::now()->addMonthsNoOverflow(3)->toDateString();
            $data['customer_id'] = $customer->id;

            // Remove temporary form fields
            unset($data['customer_name'], $data['customer_mobile']);

            return $data;
        });
    }
}
