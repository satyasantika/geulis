<?php

namespace App\Filament\Resources\ContentVariants\Schemas;

use App\Models\LessonUnit;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * A-01 Varian konten. Sumbu level × modus boleh berisi '*' (berlaku semua)
 * supaya tim cukup menulis 3–5 varian per unit, bukan 9.
 */
class ContentVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('lesson_unit_id')
                ->label('Unit')
                ->options(fn () => LessonUnit::query()->with('meeting')->get()
                    ->sortBy(fn (LessonUnit $u) => $u->meeting->urutan * 10 + $u->urutan)
                    ->mapWithKeys(fn (LessonUnit $u) => [$u->getKey() => "P{$u->meeting->urutan}.{$u->urutan} {$u->judul} ({$u->tipe})"]))
                ->searchable()
                ->required(),
            Select::make('level')->label('Level')
                ->options(['*' => '* (semua level)', 'L1' => 'L1 Dasar', 'L2' => 'L2 Berkembang', 'L3' => 'L3 Mahir'])
                ->default('*')->required(),
            Select::make('modus')->label('Modus')
                ->options(['*' => '* (semua modus)', 'visual' => 'Visual-Manipulatif', 'simbolik' => 'Simbolik-Analitis', 'naratif' => 'Naratif-Kontekstual'])
                ->default('*')->required(),
            TextInput::make('judul')->label('Judul varian')->required()->maxLength(255)->columnSpanFull(),
            RichEditor::make('badan_konten')->label('Isi konten')->columnSpanFull(),
            Select::make('cultural_asset_id')->label('Aset budaya (jangkar)')->relationship('culturalAsset', 'nama_motif')->searchable()->preload(),
            Select::make('status')->label('Status')->options(['draf' => 'Draf', 'revisi' => 'Revisi', 'siap' => 'Siap'])->default('draf')->required(),
            Section::make('GeoGebra (opsional)')
                ->description('Applet dimuat dari server sendiri. Perintah GeoGebra dijalankan berurutan saat applet siap.')
                ->schema([
                    Select::make('konfigurasi_geogebra.app')->label('Aplikasi')
                        ->options(['geometry' => 'Geometry', 'graphing' => 'Graphing', 'classic' => 'Classic'])
                        ->default('geometry'),
                    Toggle::make('konfigurasi_geogebra.terpandu')->label('Mode terpandu (alat dibatasi, tanpa menu)')->default(true),
                    Textarea::make('konfigurasi_geogebra.perintah')->label('Perintah awal (satu per baris)')
                        ->placeholder("A=(1,2)\nB=(3,4)\ng: y = x\nA'=Reflect(A, g)")
                        ->rows(5)->columnSpanFull(),
                    TagsInput::make('konfigurasi_geogebra.alat')->label('Alat yang diaktifkan (ID toolbar GeoGebra)')
                        ->placeholder('0, 1, 39')->helperText('Kosongkan untuk toolbar bawaan aplikasi.')->columnSpanFull(),
                ])->columns(2)->columnSpanFull()->collapsible(),
        ])->columns(2);
    }
}
