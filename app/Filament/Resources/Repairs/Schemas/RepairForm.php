<?php

namespace App\Filament\Resources\Repairs\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class RepairForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /* ---------------- Thumbnail preview (read-only) ---------------- */
            Placeholder::make('thumb_preview')
                ->label('Image')
                ->content(function ($record) {
                    // Use your preferred fallback
                    $fallback = 'https://as1.ftcdn.net/v2/jpg/04/62/93/66/1000_F_462936689_BpEEcxfgMuYPfTaIAOC1tCDurmsno7Sp.jpg';

                    if (! $record) {
                        return new HtmlString('<img src="'.$fallback.'" class="h-14 w-14 rounded object-cover" loading="lazy">');
                    }

                    // Prefer first repair image
                    $path = data_get($record, 'images.0.url')
                        ?? optional($record->images()->oldest()->first())->url;

                    if (! $path) {
                        $url = $fallback;
                    } elseif (Str::startsWith($path, ['http://', 'https://'])) {
                        $url = $path;
                    } else {
                        // normalize: remove "storage/" prefix if present
                        $relative = ltrim(preg_replace('#^/?storage/#', '', $path), '/');
                        // also remove "public/" if someone saved that
                        $relative = ltrim(preg_replace('#^public/#', '', $relative), '/');

                        $url = Storage::disk('public')->exists($relative)
                            ? Storage::disk('public')->url($relative)
                            : $fallback;
                    }

                    return new HtmlString('<img src="'.$url.'" class="h-14 w-14 rounded object-cover" loading="lazy">');
                })
                ->columnSpanFull(),

            /* ---------------- Customer Info (text inputs only) ---------------- */
            TextInput::make('customer_name')
                ->label('Customer Name')
                ->required()
                ->maxLength(120)
                ->placeholder('Juan Dela Cruz'),

            TextInput::make('customer_mobile')
                ->label('Mobile Number')
                ->required()
                ->maxLength(11)
                ->placeholder('09XXXXXXXXX or 9XXXXXXXXX')
                ->helperText('Accepts 09XXXXXXXXX or 9XXXXXXXXX (PH mobile only)')
                // mask keeps user input numeric (up to 11). 10-digit entries still pass regex below.
                ->mask('99999999999')
                ->rule('regex:/^(09\d{9}|9\d{9})$/')
                ->validationMessages([
                    'regex' => 'Enter a valid PH mobile number (09XXXXXXXXX or 9XXXXXXXXX).',
                    'max'   => 'Mobile number must be 10 or 11 digits long.',
                ]),

            /* ---------------- Repair Details ---------------- */
            TextInput::make('price')
                ->label('Repair Cost (₱)')
                ->required()
                ->numeric()
                ->default(0.0)
                ->prefix('₱'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'pending'     => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed'   => 'Completed',
                    'cancelled'   => 'Cancelled',
                ])
                ->default('pending')
                ->required(),

            Textarea::make('description')
                ->label('Description / Notes')
                ->default(null)
                ->rows(3)
                ->columnSpanFull()
                ->placeholder('Enter any notes or description about the repair...')
                ->required(),

            /* ---------------- Single Image Uploader ---------------- */
            Repeater::make('images')
                ->label('Repair Images')
                ->relationship('images')
                ->columns(1)
                ->deletable(false)   // no trash icon on the item
                ->addable(false)     // hide “Add” (limit to 1)
                ->reorderable(false) // no drag handle
                ->columnSpanFull()
                ->schema([
                    FileUpload::make('url')
                        ->label('')            // no label above uploader
                        ->image()
                        ->disk('public')       // storage/app/public
                        ->directory('repairs') // stored as "repairs/..."
                        ->visibility('public')
                        ->preserveFilenames()
                        ->previewable(true)
                        ->openable()
                        ->downloadable()
                        ->columnSpanFull(),
                ])
                ->maxItems(1)
                // Show on Create & Edit; hide on View page if you want read-only UI there
                ->hidden(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ViewRecord),
        ]);
    }
}
