<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    /**
     * Konfigurasi Motif Builder ditulis sebagai JSON mentah di formulir.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['tipe'] ?? null) === 'motif_builder' && filled($this->data['konfigurasi_motif'] ?? null)) {
            $data['konfigurasi'] = json_decode($this->data['konfigurasi_motif'], true) ?? [];
        }

        return $data;
    }
}
