<?php

namespace App\Filament\Resources\Transactions\Tables;


use App\Models\Transaction;
use App\Models\Transaction_Item;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;


class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ✅ Eager-load items + product/pawn/repair AND pull a SUM of line_total
            ->modifyQueryUsing(function ($query) {
                $query->with([
                    'items.product:id,name',
                    'items.pawnItem:id,title',
                    'items.repair:id', // add more columns if you want
                    'customer:id,name',
                    'staff:id,name',
                ])->withSum('items', 'line_total'); // gives items_sum_line_total
            })
            ->columns([

                BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'success' => 'buy',
                        'warning' => 'pawn',
                        'info'    => 'repair',
                        'danger'  => 'sell',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),

                // ✅ ITEMS LIST (comma-separated, grouped)
                TextColumn::make('items_list')
                    ->label('Items')
                    ->getStateUsing(function ($record) {
                        return $record->items
                            ->map(function ($it) {
                                if ($it->product)  return $it->product->name . ' x' . (int) $it->quantity;
                                if ($it->pawnItem) return ($it->pawnItem->title ?? 'Pawn Item') . ' x' . (int) $it->quantity;
                                if ($it->repair)   return 'Repair #' . $it->repair_id . ' x' . (int) $it->quantity;
                                return 'Item #' . $it->id;
                            })
                            ->implode(', ');
                    })
                    ->wrap()
                    ->toggleable(),

                // ✅ TOTAL = SUM(transaction_items.line_total)
                TextColumn::make('items_sum_line_total')
                    ->label('Total Amount')
                    ->money('PHP', true)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'buy' => 'Buy', 'sell' => 'Sell', 'pawn' => 'Pawn', 'repair' => 'Repair',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([ DeleteBulkAction::make() ]),
            ]);
    }
}
