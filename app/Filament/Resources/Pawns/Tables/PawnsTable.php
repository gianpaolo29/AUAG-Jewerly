<?php

namespace App\Filament\Resources\Pawns\Tables;

use App\Models\Transaction;
use App\Models\Transaction_Item; // keep this if your class is Transaction_Item
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

class PawnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Principal')
                    ->money('PHP', true)
                    ->sortable(),

                TextColumn::make('interest_cost')
                    ->label('Interest')
                    ->money('PHP', true)
                    ->sortable(),

                // To Pay today (your computed field)
                TextColumn::make('payable_today')
                    ->label('To Pay (₱)')
                    ->money('PHP', true)
                    ->getStateUsing(fn ($record) => $record->payable_today)
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('M d, Y')
                    ->sortable(),

                BadgeColumn::make('is_overdue')
                    ->label('Overdue')
                    ->getStateUsing(fn ($record) => $record->is_overdue ? 'Yes' : 'No')
                    ->colors([
                        'danger' => 'Yes',
                        'success' => 'No',
                    ]),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'redeemed',
                        'danger'  => 'forfeited',
                    ])
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

            ->recordActions([
                EditAction::make(),

                Action::make('redeem_cash')
                    ->label('Redeem')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn () => true)
                    ->disabled(fn ($record) => in_array($record->status, ['redeemed', 'forfeited']))
                    ->color(fn ($record) => in_array($record->status, ['redeemed', 'forfeited']) ? 'gray' : 'success')
                    ->tooltip(fn ($record) => in_array($record->status, ['redeemed', 'forfeited'])
                        ? 'Cannot redeem: already ' . $record->status . '.'
                        : 'Redeem this pawn (cash).')
                    ->action(function ($record) {
                        $user = auth()->user();

                        // If admin, leave staff null
                        $isAdmin =
                            (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
                            (isset($user->role) && in_array($user->role, ['admin', 'superadmin'])) ||
                            (isset($user->is_admin) && (bool) $user->is_admin === true);

                        $staffId = $isAdmin ? null : ($user?->id);

                        $amount = (float) (
                            $record->payable_today
                            ?? $record->total_due
                            ?? (($record->price ?? 0) + ($record->interest_cost ?? 0))
                        );

                        if ($amount <= 0) {
                            Notification::make()
                                ->title('Nothing to collect')
                                ->body('Total due is zero or negative.')
                                ->danger()
                                ->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $staffId, $amount) {
                            // ✅ 1) Create the Transaction (goes to `transactions`)
                            $tx = Transaction::create([
                                'customer_id' => $record->customer_id,
                                'staff_id'    => 3,
                                'type'        => 'pawn',
                            ]);

                            // ✅ 2) Create Transaction Item (goes to `transaction_items`)
                            Transaction_Item::create([
                                'transaction_id' => $tx->id,
                                'product_id'     => null,
                                'pawn_item_id'   => $record->id,
                                'repair_id'      => null,
                                'quantity'       => 1,
                                'unit_price'     => $amount,
                                'line_total'     => $amount,
                            ]);

                            // ✅ 3) Update Pawn status
                            $record->update([
                                'status'      => 'redeemed',
                                'redeemed_at' => now(),
                            ]);
                        });

                        Notification::make()
                            ->title('Redeemed (cash)')
                            ->body('Transaction and item recorded. Pawn marked as redeemed.')
                            ->success()
                            ->send();
                    }),
            ])

            ->filters([
                Tables\Filters\TernaryFilter::make('overdue')
                    ->label('Overdue')
                    ->queries(
                        true: fn ($q) => $q->where('status', 'active')
                            ->whereDate('due_date', '<', now()->toDateString()),
                        false: fn ($q) => $q->where(function ($x) {
                            $x->whereNull('due_date')
                              ->orWhereDate('due_date', '>=', now()->toDateString());
                        }),
                    ),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
