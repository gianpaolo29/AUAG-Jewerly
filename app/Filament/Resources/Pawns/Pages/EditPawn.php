<?php

namespace App\Filament\Resources\Pawns\Pages;

use App\Filament\Resources\Pawns\PawnResource;
use App\Models\Customer;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditPawn extends EditRecord
{
    protected static string $resource = PawnResource::class;

    /**
     * Prefill customer text inputs on edit.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $pawn = $this->getRecord();

        if ($pawn && $pawn->customer) {
            $data['customer_name']   = $pawn->customer->name;
            $data['customer_mobile'] = $pawn->customer->mobile_number;
        }

        return $data;
    }

    /**
     * Update or relink the Customer using the text inputs.
     * Keep due_date as originally set by Create page (3 months rule).
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $pawn = $this->getRecord();

            // Guard: preserve the original due date (don't let UI change it)
            if ($pawn && $pawn->due_date) {
                $data['due_date'] = $pawn->due_date->toDateString();
            }

            // If customer text inputs were provided, sync them
            if (! empty($data['customer_name']) || ! empty($data['customer_mobile'])) {
                $target = ! empty($data['customer_mobile'])
                    ? Customer::where('mobile_number', $data['customer_mobile'])->first()
                    : null;

                if ($target && $target->id !== $pawn->customer_id) {
                    // Mobile belongs to another customer: switch association
                    $pawn->customer()->associate($target);
                } else {
                    // Update current linked customer (or create if missing)
                    $cust = $pawn->customer ?? new Customer();
                    if (! empty($data['customer_name']))   $cust->name = $data['customer_name'];
                    if (! empty($data['customer_mobile'])) $cust->mobile_number = $data['customer_mobile'];
                    $cust->save();

                    $pawn->customer()->associate($cust);
                }

                $pawn->save();
            }

            unset($data['customer_name'], $data['customer_mobile']);

            return $data;
        });
    }
}
