<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\Pengguna\PembacaTeksPengguna;
use App\Services\Pengguna\PendaftarPengguna;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imporMassal')
                ->label('Impor massal')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->schema([
                    Textarea::make('daftar')
                        ->label('Daftar pengguna')
                        ->helperText('Satu baris satu akun: role, username, nama, password. Username siswa = NIS, username guru = NIP. NIS yang sudah ada tidak dibuat ulang — peran ditambahkan ke akun itu.')
                        ->placeholder("siswa,0056781234,Reza Pratama,siswa-1234\nguru,197812312345,Vepi Nurhasanah,password")
                        ->rows(12)
                        ->required()
                        ->extraInputAttributes(['class' => 'font-mono text-sm']),
                ])
                ->modalHeading('Impor massal pengguna')
                ->modalSubmitActionLabel('Impor')
                ->action(function (array $data, PembacaTeksPengguna $pembaca, PendaftarPengguna $pendaftar): void {
                    $hasil = $pembaca->baca($data['daftar']);
                    $galat = $hasil['galat'];

                    if ($hasil['baris'] === []) {
                        Notification::make()
                            ->title('Tidak ada baris yang bisa diimpor.')
                            ->body($galat === [] ? 'Tempel daftar dengan format role, username, nama, password.' : implode("\n", $galat))
                            ->danger()
                            ->send();

                        return;
                    }

                    $daftar = $pendaftar->daftarkanBanyak($hasil['baris']);
                    $galat = [...$galat, ...$daftar['galat']];
                    $judul = sprintf('%d pengguna baru dibuat, %d sudah terdaftar sebelumnya.', $daftar['dibuat'], $daftar['sudah_ada']);

                    Notification::make()
                        ->title($judul)
                        ->body($galat === [] ? null : implode("\n", $galat))
                        ->{$galat === [] ? 'success' : 'warning'}()
                        ->send();
                }),
            CreateAction::make()->label('Buat pengguna'),
        ];
    }
}
