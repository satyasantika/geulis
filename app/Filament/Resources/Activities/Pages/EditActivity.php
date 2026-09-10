<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Konfigurasi Motif Builder ditulis sebagai JSON mentah di formulir.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['tipe'] ?? null) === 'motif_builder' && filled($this->data['konfigurasi_motif'] ?? null)) {
            $data['konfigurasi'] = json_decode($this->data['konfigurasi_motif'], true) ?? [];
        }

        return $data;
    }
}
