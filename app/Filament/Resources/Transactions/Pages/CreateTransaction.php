<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
    
    protected function afterCreate(): void
{
    $tx = $this->record;

    if ($tx->type === 'buy') {
        foreach ($tx->items as $item) {
            if ($item->product_id && $item->quantity > 0) {
                $product = \App\Models\Product::find($item->product_id);
                if ($product) {
                    // Customer buys from store → subtract stock (never below 0)
                    $product->quantity = max(0, (int) $product->quantity - (int) $item->quantity);
                    $product->save();
                }
            }
        }
    }
}

    
}
