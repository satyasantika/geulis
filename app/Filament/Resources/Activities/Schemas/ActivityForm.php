<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Models\LessonUnit;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * Aktivitas per unit. Kuis dipakai untuk latihan berjenjang dan pemeriksaan
 * penguasaan (3–5 butir). Konfigurasi Motif Builder diisi pada Sprint 3.
 */
class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('lesson_unit_id')
                ->label('Unit')
                ->options(fn () => LessonUnit::query()->with('meeting')->get()
                    ->sortBy(fn (LessonUnit $u) => $u->meeting->urutan * 10 + $u->urutan)
                    ->mapWithKeys(fn (LessonUnit $u) => [$u->getKey() => "P{$u->meeting->urutan}.{$u->urutan} {$u->judul} ({$u->tipe})"]))
                ->searchable()->required(),
            Select::make('tipe')->label('Tipe')
                ->options(['kuis' => 'Kuis pilihan ganda', 'geogebra' => 'GeoGebra', 'motif_builder' => 'Motif Builder', 'unggah' => 'Unggah produk', 'refleksi' => 'Refleksi'])
                ->default('kuis')->required()->live(),
            TextInput::make('judul')->label('Judul')->required()->maxLength(255),
            Select::make('level')->label('Level')->options(['*' => '* (semua)', 'L1' => 'L1', 'L2' => 'L2', 'L3' => 'L3'])->default('*')->required(),
            TextInput::make('skor_maks')->label('Skor maksimum')->numeric()->default(100)->required(),
            Section::make('Butir kuis')
                ->visible(fn (Get $get): bool => $get('tipe') === 'kuis')
                ->schema([
                    Repeater::make('konfigurasi.butir')->label('')
                        ->schema([
                            Textarea::make('pertanyaan')->label('Pertanyaan')->rows(2)->required(),
                            TagsInput::make('pilihan')->label('Pilihan (urut A, B, C, D)')->required()->placeholder('Ketik lalu Enter'),
                            TextInput::make('kunci')->label('Indeks kunci (0 = pilihan pertama)')->numeric()->minValue(0)->maxValue(5)->required(),
                            TextInput::make('label')->label('Label konsep (untuk butir salah)')->placeholder('tanda ordinat')->maxLength(60),
                        ])->columns(2)->minItems(1)->maxItems(10)->reorderable()->collapsible(),
                ])->columnSpanFull(),
            Section::make('Pertanyaan refleksi')
                ->visible(fn (Get $get): bool => $get('tipe') === 'refleksi')
                ->schema([
                    TagsInput::make('konfigurasi.pertanyaan')->label('Pertanyaan terbuka (2)')->placeholder('Ketik lalu Enter'),
                ])->columnSpanFull(),
            Section::make('Motif Builder')
                ->visible(fn (Get $get): bool => $get('tipe') === 'motif_builder')
                ->schema([
                    Textarea::make('konfigurasi.json')->label('Konfigurasi JSON (motif sasaran, blok, kunci)')->rows(8)
                        ->helperText('Diisi pada Sprint 3; struktur lihat docs.')->columnSpanFull(),
                ])->columnSpanFull(),
        ])->columns(2);
    }
}
