<?php

namespace App\Filament\Resources\Repairs\Tables;

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


class RepairsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Repair Cost')
                    ->money('PHP', true),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',      
                        'info'    => 'in_progress', 
                        'success' => 'completed',   
                        'danger'  => 'cancelled',    
                    ])
                    ->formatStateUsing(fn ($state) => str($state)->headline())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'      => 'Pending',
                        'in_progress'  => 'In Progress',
                        'completed'    => 'Completed',
                        'cancelled'    => 'Cancelled',
                    ]),
            ])
              ->recordActions([
                EditAction::make(),

                Action::make('mark_done')
                    ->label('Mark as Done')
                    ->icon('heroicon-o-check-circle')
                    ->color(fn ($record) => in_array($record->status, ['completed', 'cancelled']) ? 'gray' : 'success')
                    ->visible(fn () => true)
                    ->disabled(fn ($record) => in_array($record->status, ['completed', 'cancelled']))
                    ->tooltip(fn ($record) => in_array($record->status, ['completed', 'cancelled'])
                        ? 'Already ' . ucfirst($record->status)
                        : 'Mark repair as completed and save transaction')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $user = auth()->user();

                        $isAdmin =
                            (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
                            (isset($user->role) && in_array($user->role, ['admin', 'superadmin'])) ||
                            (isset($user->is_admin) && (bool) $user->is_admin === true);

                        $staffId = $isAdmin ? null : ($user?->id);

                        $amount = (float) ($record->price ?? 0);

                        if ($amount <= 0) {
                            Notification::make()
                                ->title('No amount to save')
                                ->body('Repair cost is zero or missing.')
                                ->danger()
                                ->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $staffId, $amount) {
                
                            $tx = Transaction::create([
                                'customer_id' => $record->customer_id,
                                'staff_id'    => $staffId,  
                                'type'        => 'repair',
                            ]);

                          
                            Transaction_Item::create([
                                'transaction_id' => $tx->id,
                                'product_id'     => null,
                                'pawn_item_id'   => null,
                                'repair_id'      => $record->id,
                                'quantity'       => 1,
                                'unit_price'     => $amount,
                                'line_total'     => $amount,
                            ]);

                   
                            $record->update([
                                'status'        => 'completed',
                                'completed_at'  => now(), 
                            ]);
                        });

                        Notification::make()
                            ->title('Repair completed')
                            ->body('Transaction saved and repair marked as completed.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
