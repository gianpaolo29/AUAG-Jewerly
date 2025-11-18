<?php

namespace App\Filament\Resources\Repairs\Pages;

use App\Filament\Resources\Repairs\RepairResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EditRepair extends EditRecord
{
    protected static string $resource = RepairResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Normalize legacy paths (and typos) so FileUpload previews work.
     * IMPORTANT: FileUpload expects a *relative* path on the configured disk.
     * Your working folder is "repair", so convert other variants to that.
     */
    protected function normalizePath(?string $path): ?string
    {
        if (blank($path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        // Common cleanup & typo fixes
        $replacements = [
            'public/'   => '',
            '/storage/' => '',
            'storage/'  => '',
            // Folder variants → force to "repair/"
            'repairs/'  => 'repair/',
            'rapairs/'  => 'repair/',
        ];

        foreach ($replacements as $from => $to) {
            $path = Str::replaceFirst($from, $to, $path);
        }

        // If a full URL, trim to relative if it contains /storage/...
        if (Str::startsWith($path, ['http://', 'https://'])) {
            if (($pos = stripos($path, '/storage/')) !== false) {
                $path = substr($path, $pos + strlen('/storage/')); // e.g. "repair/foo.jpg"
            } else {
                // External URL that isn't our storage: keep as-is (won't preview in FileUpload)
                return $path;
            }
        }

        return ltrim($path, '/');
    }

    /**
     * Return image URL or fallback if missing on disk (use in a Placeholder/View).
     * Do NOT feed this back into FileUpload state — it needs a relative path.
     */
    protected function getImageUrlWithFallback(?string $path): string
    {
        $normalized = $this->normalizePath($path);

        if ($normalized && Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->url($normalized);
        }

        return asset('images/not-found.jpg'); // your fallback
    }

    /**
     * Prefill customer fields + normalize image paths for FileUpload preview.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $repair = $this->getRecord();

        if ($repair && $repair->customer) {
            $data['customer_name']   = $repair->customer->name;
            $data['customer_mobile'] = $repair->customer->mobile_number;
        }

        // ✅ Keep FileUpload state as relative paths (no full URLs here)
        if (! empty($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as &$img) {
                $img['url'] = $this->normalizePath(Arr::get($img, 'url'));
            }
            unset($img);
        }

        return $data;
    }

    /**
     * Update/link Customer + normalize image paths before save.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $repair = $this->getRecord();

            // Normalize images so DB stores "repair/filename.jpg"
            if (! empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as &$img) {
                    $img['url'] = $this->normalizePath(Arr::get($img, 'url'));
                }
                unset($img);
            }

            // Sync customer info
            if (! empty($data['customer_name']) || ! empty($data['customer_mobile'])) {
                $target = ! empty($data['customer_mobile'])
                    ? Customer::where('mobile_number', $data['customer_mobile'])->first()
                    : null;

                if ($target && $target->id !== $repair->customer_id) {
                    $repair->customer()->associate($target);
                } else {
                    $cust = $repair->customer ?? new Customer();
                    if (! empty($data['customer_name']))   $cust->name = $data['customer_name'];
                    if (! empty($data['customer_mobile'])) $cust->mobile_number = $data['customer_mobile'];
                    $cust->save();
                    $repair->customer()->associate($cust);
                }

                $repair->save();
            }

            unset($data['customer_name'], $data['customer_mobile']);

            return $data;
        });
    }
}
