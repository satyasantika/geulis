<?php

namespace App\Filament\Resources\SimulationRuns\Pages;

use App\Filament\Resources\SimulationRuns\SimulationRunResource;
use App\Models\SimulationRun;
use App\Services\Simulasi\PembuatSimulasi;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSimulationRun extends CreateRecord
{
    protected static string $resource = SimulationRunResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(PembuatSimulasi::class)->buat(
            (int) $data['jumlah_kelas'],
            (int) $data['jumlah_guru'],
            (int) $data['jumlah_siswa'],
            auth()->user(),
        );
    }

    protected function getRedirectUrl(): string
    {
        return SimulationRunResource::getUrl('index');
    }

    /**
     * @param  SimulationRun  $record
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Simulasi siap. Buka Layar QR, lalu minta peserta memindai berurutan.';
    }
}
